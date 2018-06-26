<?php

namespace Manix\Brat\Utility\HTTP;

use Manix\Brat\Components\Controller;
use Manix\Brat\Components\Validation\Ruleset;

class HTTPController extends Controller {

  /**
   * Determine whether to turn on CSRF protection
   * @param string $method
   * @return boolean
   */
  public function csrf($method) {
    switch ($method) {
      case 'post':
      case 'put':
      case 'delete':
        return true;

      default:
        return false;
    }
  }

  /**
   * Determine whether to turn on session
   * @param string $method
   * @return boolean
   */
  public function session($method) {
    if ($method) {
      return true;
    }
  }

  /**
   * Apply rules to the $_GET array
   * @param Ruleset $rules
   * @return Ruleset
   */
  public function query(Ruleset $rules): Ruleset {
    return $rules;
  }

}
