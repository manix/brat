<?php

namespace Manix\Brat\Components\Errors;

use Exception as E;
use Manix\Brat\Components\Controller;

class Exception extends E {

  protected $handler;

  /**
   * @param Controller $controller The login controller to render
   */
  public function setHandler(Controller $controller) {
    $this->handler = $controller;
    return $this;
  }

  public function getHandler(): Controller {
    return $this->handler;
  }
}
