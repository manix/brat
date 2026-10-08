<?php

namespace Manix\Brat\Utility\Users\Models;

use Manix\Brat\Components\Model;

/**
 * Codes from an authenticator app (TOTP, RFC 6238): the app and the server share a secret
 * and both compute a 6 digit code from it for every 30 seconds. `data` holds the secret in
 * base32, as the apps take it. `used` is the last 30 second step whose code was accepted:
 * a code is accepted once, and never one older than the last accepted.
 */
class MFATotp implements MFAMethod {

  const PERIOD = 30;
  const DIGITS = 6;
  // codes of this many steps before and after the current one are accepted too, for phones whose clock is a little off
  const DRIFT = 1;
  const BASE32 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

  /**
   * A new secret: 20 random bytes (160 bits, as RFC 4226 recommends) in base32, 32 characters.
   */
  public static function secret(): string {
    $bits = '';
    foreach (str_split(random_bytes(20)) as $byte) {
      $bits .= str_pad(decbin(ord($byte)), 8, '0', STR_PAD_LEFT);
    }

    $secret = '';
    foreach (str_split($bits, 5) as $chunk) {
      $secret .= self::BASE32[bindec($chunk)];
    }

    return $secret;
  }

  /**
   * The address an authenticator app adds the account from, usually shown as a QR code.
   */
  public static function uri(string $secret, string $account, string $issuer): string {
    return 'otpauth://totp/' . rawurlencode($issuer) . ':' . rawurlencode($account) . '?' . http_build_query([
        'secret' => $secret,
        'issuer' => $issuer,
        'digits' => self::DIGITS,
        'period' => self::PERIOD
    ], '', '&', PHP_QUERY_RFC3986);
  }

  public function challenge(Model $record) {
    // the app shows the code by itself
  }

  public function verify(Model $record, string $code): bool {
    if (strlen($code) !== self::DIGITS || !ctype_digit($code)) {
      return false;
    }

    // the secret's bytes from base32
    $bits = '';
    foreach (str_split($record->data) as $char) {
      $bits .= str_pad(decbin(strpos(self::BASE32, $char)), 5, '0', STR_PAD_LEFT);
    }
    $key = '';
    foreach (str_split($bits, 8) as $byte) {
      if (strlen($byte) === 8) {
        $key .= chr(bindec($byte));
      }
    }

    $now = intdiv(time(), self::PERIOD);
    $used = (int)$record->used;

    for ($step = max($now - self::DRIFT, $used + 1); $step <= $now + self::DRIFT; $step++) {
      // HOTP (RFC 4226) of the step: 4 bytes of the HMAC, at the offset its last 4 bits give
      $hash = hash_hmac('sha1', pack('J', $step), $key, true);
      $value = unpack('N', substr($hash, ord($hash[19]) & 0xf, 4))[1] & 0x7fffffff;

      if (hash_equals(str_pad($value % 10 ** self::DIGITS, self::DIGITS, '0', STR_PAD_LEFT), $code)) {
        $record->used = $step;

        return true;
      }
    }

    return false;
  }

}
