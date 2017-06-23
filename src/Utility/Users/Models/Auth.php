<?php

namespace Manix\Brat\Utility\Users\Models;

use Manix\Brat\Components\Model;

class Auth {

  private function __construct() {
    
  }

  /**
   * @var AuthManager
   */
  protected static $manager;

  public static function registerManager(AuthManager $manager) {
    self::$manager = $manager;
  }

  /**
   * Retrieve the cached user associated with the current session.
   * @return Model The user.
   */
  public static function user() {
    return self::$manager->user();
  }

  /**
   * Register the current session to a user.
   * @param User $user
   */
  public static function register(User $user) {
    return self::$manager->register($user);
  }

  /**
   * Update the cached user object.
   * @param User $user
   */
  public static function updateCache(User $user) {
    return self::$manager->updateCache($user);
  }

  public static function getCached($id) {
    return self::$manager->getCached($id);
  }

  /**
   * Log the user out.
   */
  public static function destroy() {
    return self::$manager->destroy();
  }

  /**
   * Redirect to login page if user is not logged in.
   * @param string $backto Address to go back to after successful login.
   */
  public static function required() {
    return self::$manager->required();
  }

  /**
   * Directly return a property from the cached user object.
   * @param string $name The property
   * @param array $arguments unused.
   * @return mixed The value.
   */
  public static function __callStatic($name, $arguments) {
    return self::$manager->user()->$name;
  }

  public static function issueRememberToken() {
    return self::$manager->issueRememberToken();
  }

}

Auth::registerManager(new AuthManager());