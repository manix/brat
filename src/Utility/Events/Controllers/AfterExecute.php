<?php

namespace Manix\Brat\Utility\Events\Controllers;

use Manix\Brat\Components\Controller;
use Manix\Brat\Components\Events\Event;

class AfterExecute extends Event {

  protected $controller;
  protected $data;

  function __construct(Controller $controller, $data) {
    $this->controller = $controller;
    $this->data = $data;
  }

  function getController() {
    return $this->controller;
  }

  function getData() {
    return $this->data;
  }

}
