<?php

namespace Manix\Brat\Components\Errors;

use Exception as E;
use Manix\Brat\Components\Controller;

class Exception extends E {

  // its message is shown to the user, the answer to the ultimate question (under 100, so the response is a 500)
  const DISPLAY_CODE = 42;

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
