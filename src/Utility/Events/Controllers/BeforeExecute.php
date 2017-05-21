<?php

namespace Manix\Brat\Utility\Events\Controllers;

use Manix\Brat\Components\Controller;
use Manix\Brat\Components\Events\Event;

class BeforeExecute extends Event {

  protected $controller;
  protected $method;

  function __construct(Controller $controller, $method) {
    $this->controller = $controller;
    $this->method = $method;
  }

  function getController() {
    return $this->controller;
  }

  function getMethod() {
    return $this->method;
  }

}
