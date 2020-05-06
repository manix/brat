<?php

namespace Manix\Brat\Components\Errors;

use Exception as E;
use Manix\Brat\Components\Controller;

class Exception extends E {

  const DISPLAY_CODE = 666;

  protected $handler;

  /**
   * @param Controller $controller The controller to render
   */
  public function setHandler(Controller $controller) {
    $this->handler = $controller;
    return $this;
  }

  public function getHandler(): Controller {
    return $this->handler ?? new Handler($this);
  }

}
