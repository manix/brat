<?php

namespace Manix\Brat\Components;

interface Middleware {

  public function execute(Controller $controller, $method, Program $program);
}
