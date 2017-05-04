<?php

namespace Manix\Brat\Components;

use Exception;

abstract class Controller {

  use Translator;

  /**
   * @var string Name of the page that this controller will render.
   */
  public $page;

  /**
   * @var array An array of common data to return to each request.
   */
  protected $data = [];
  protected $listeners = [];

  /**
   * Gets called before the program executes a method on the controller.
   * @param string $method The method that is about to get executed.
   * @return string The method that will be executed.
   */
  public function before($method) {
    return $method;
  }

  /**
   * Gets called after the program has executed a method on the controller.
   * @param mixed $data The data returned by the executed method.
   * @return mixed $data The data that will be used when constructing the response.
   */
  public function after($data) {
    if (is_array($data)) {
      $data = array_merge($this->data, $data);
    }

    registry('page', $this->page);
    
    return $data;
  }

  public function data() {
    return $this->data;
  }

  public function __call($name, $arguments) {
    if (isset($name) && isset($arguments)) {
      throw new Exception('Method not found.', 404);
    }
  }

  public final function execute($method) {
    return $this->after($this->{$this->before($method)}());
  }
}
