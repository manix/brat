<?php

namespace Manix\Brat\Utility\Users\Models;

use Manix\Brat\Components\Model;
use Manix\Brat\Utility\Users\Controllers\Login;
use const MANIX;
use function cache;
use function url;

class Auth {

  private function __construct() {
    
  }

  protected static $user;

  /**
   * Retrieve the cached user associated with the current session.
   * @return Model The user.
   */
  public static function user() {
    if (self::$user === null) {
      $id = ($_SESSION[MANIX]['auth'] ?? 0);

      if ($id) {
        self::$user = cache('users/auth/' . $id);

        // Extend the ttl for the cached user if it's about to expire soon.
        if (self::$user === null) {
          self::register((new UserGateway())->find($id)->first());
        }
      } else {
        // Not logged in
        self::$user = false;
      }
    }

    return self::$user;
  }

  /**
   * Register the current session to a user.
   * @param Model $user
   */
  public static function register(Model $user) {
    $_SESSION[MANIX]['auth'] = $user->id;
    self::updateCache($user);
    self::$user = $user;
  }

  public static function updateCache(User $user) {
    cache('users/auth/' . $user->id, $user, 1800);
  }

  public static function getCached($id) {
    return cache('users/auth/' . $id);
  }

  /**
   * Log the user out.
   */
  public static function destroy() {
    unset($_SESSION[MANIX]['auth']);
    self::$user = null;
  }

  /**
   * Redirect to login page if user is not logged in.
   * @param string $backto Address to go back to after successful login.
   */
  public static function required() {
    if (self::user() === false) {
      global $manix;

      $controller = new Login(url());

      http_response_code(403);
      exit($manix->program()->respond($controller->execute('get')));
    }
  }

  /**
   * Directly return a property from the cached user object.
   * @param string $name The property
   * @param array $arguments unused.
   * @return mixed The value.
   */
  public static function __callStatic($name, $arguments) {

    return self::user()->$name;
  }

}
