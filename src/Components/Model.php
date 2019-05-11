<?php

namespace Manix\Brat\Components;

class Model {

  use Translator;

  /**
   * Create a new model instance.
   * @param mixed $data Model's data.
   */
  public function __construct($data = []) {
    $this->fill($data);
  }

  /**
   * Fill the model from a source.
   * @param \Iterable $source
   */
  public function fill($source) {
    if (!empty($source)) {
      foreach ($source as $key => $value) {
        $this->$key = $value;
      }
    }
  }

}
