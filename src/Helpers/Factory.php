<?php

namespace Manix\Brat\Helpers;

trait Factory {

  public function instantiate($class, $data = []) {
    return new $class(...$data);
  }

}
