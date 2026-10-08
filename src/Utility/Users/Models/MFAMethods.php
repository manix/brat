<?php

namespace Manix\Brat\Utility\Users\Models;

use Exception;

class MFAMethods {

  /**
   * The MFA methods by name. A project lists its own in config/mfa.php:
   * ['methods' => ['totp' => MFATotp::class, ...]]
   */
  const DEFAULTS = [
      'totp' => MFATotp::class
  ];

  /**
   * The method named $name, or null when the project doesn't offer it.
   */
  public static function get(string $name): ?MFAMethod {
    try {
      $methods = config('mfa')['methods'];
    } catch (Exception $e) {
      $methods = self::DEFAULTS;
    }

    return isset($methods[$name]) ? new $methods[$name] : null;
  }

}
