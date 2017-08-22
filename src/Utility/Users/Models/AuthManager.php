<?php

namespace Manix\Brat\Utility\Users\Models;

use Manix\Brat\Components\Model;
use Manix\Brat\Utility\Users\Controllers\Login;
use Manix\Brat\Utility\Users\Controllers\UserGatewayFactory;
use const MANIX;
use const SITE_DOMAIN;
use function cache;
use function url;

class AuthManager {

  use UserGatewayFactory;

  protected $user;

  /**
   * Retrieve the cached user associated with the current session.
   * @return User The user.
   */
  public function user() {
    if ($this->user === null) {
      $id = ($_SESSION[MANIX]['auth'] ?? 0);

      if ($id) {
        $this->user = cache('users/auth/' . $id);

        // Extend the ttl for the cached user if it's about to expire soon.
        if (!$this->user) {
          $this->register($this->fetchUserFromPersistence($id));
        }
      } else {
        $this->user = $this->fetchUserFromPersistentLogin($_COOKIE[$this->rememberMeCookieParams(null)[0]] ?? null);

        if ($this->user) {
          $this->register($this->user);

          $this->getLoginGateway()->persist(new Model([
              'user_id' => $this->user->id,
              'ua' => $_SERVER['HTTP_USER_AGENT'] ?? null,
              'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
              'detail' => 't'
          ]));
        } else {
          $this->expireRememberCookie();
        }
      }
    }

    return $this->user;
  }

  protected function fetchUserFromPersistence($id) {
    return $this->getUserGateway()->find($id)->first();
  }

  /**
   * Validate a persistent login token and return the corresponding user.
   * @param string $token
   * @return mixed User or false
   */
  protected function fetchUserFromPersistentLogin($token) {
    $record = $this->fetchPersistentLoginTokenFromString($token);

    if (!$this->validatePersistentLoginToken($record, $token, $_SERVER['HTTP_USER_AGENT'] ?? null)) {
      return false;
    }

    return $record->user->first() ?? false;
  }

  protected function fetchPersistentLoginTokenFromString($token) {
    if (!$token) {
      return false;
    }

    $id = unpack('Lid', substr($token, 0, 4))['id'] ?? null;

    $gate = new UserTokenGateway();
    $gate->join('user');
    $record = $gate->find($id)->first();

    return $record;
  }

  protected function destroyPersistentLoginToken($token) {
    $record = $this->fetchPersistentLoginTokenFromString($token);

    if ($this->validatePersistentLoginToken($record, $token, $_SERVER['HTTP_USER_AGENT'] ?? null)) {
      $gate = new UserTokenGateway;
      $gate->wipe($record->id);
      $this->expireRememberCookie();
    }
  }

  protected function validatePersistentLoginToken($record, $token, $ua) {
    return $record && $record->validateHash(substr($token, 4)) && $record->validateUA($ua);
  }

  /**
   * Register the current session to a user.
   * @param User $user
   */
  public function register(User $user) {
    $_SESSION[MANIX]['auth'] = $user->id;
    $this->updateCache($user);
    $this->user = $user;
  }

  /**
   * Update the cached user object.
   * @param User $user
   */
  public function updateCache(User $user) {
    cache('users/auth/' . $user->id, $user, 1800);
  }

  public function getCached($id) {
    return cache('users/auth/' . $id);
  }

  /**
   * Log the user out.
   */
  public function destroy() {
    unset($_SESSION[MANIX]['auth']);
    $this->user = null;

    $this->destroyPersistentLoginToken($_COOKIE[$this->rememberMeCookieParams(null)[0]] ?? null);
  }

  /**
   * Redirect to login page if user is not logged in.
   * @param string $backto Address to go back to after successful login.
   */
  public function required() {
    if ($this->user() === false) {
      global $manix;

      $controller = new Login();

      http_response_code(403);
      exit($manix->program()->respond($controller->execute('get')));
    }
  }

  /**
   * Issue a remember-me cookie containing a persistent login token.
   */
  public function issueRememberToken() {
    $token = new UserToken();
    $token->user_id = $this->user->id;
    $token->ua = $_SERVER['HTTP_USER_AGENT'] ?? null;

    $code = random_bytes(28);

    $token->setHash($code);

    $gate = new UserTokenGateway();
    $gate->persist($token);

    $value = pack('L', $token->id) . $code;

    setcookie(...$this->rememberMeCookieParams($value));
    
    return $value;
  }

  /**
   * Return an unpackable array of arguments to be passed to setcookie(...)
   *
   * @param string $value The value argument for the cookie.
   * @return array
   */
  protected function rememberMeCookieParams($value) {
    return ['p', $value, $_SERVER['REQUEST_TIME'] + 80704000, '/', SITE_DOMAIN, false, true];
  }

  protected function expireRememberCookie() {
    setcookie($this->rememberMeCookieParams(null)[0], null, 1);
  }

}
