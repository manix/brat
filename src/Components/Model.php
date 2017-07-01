<?php

namespace Manix\Brat\Components;

class Model {

  use Translator;

  /**
   * Create a new model instance.
   * @param array $data Model's data.
   */
  public function __construct(array $data = []) {
    foreach ($data as $key => $value) {
      $this->$key = $value;
    }
  }

}
