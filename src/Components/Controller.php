<?php

namespace Manix\Brat\Components;

use Exception;
use Manix\Brat\Components\Events\EventEmitter;
use Manix\Brat\Components\Events\EventEmitterInterface;
use Manix\Brat\Components\Validation\Ruleset;
use function registry;

abstract class Controller implements EventEmitterInterface {

  use Translator,
      EventEmitter;

  /**
   * @var string Name of the page that this controller will render.
   */
  public $page;

  /**
   * @var array An array of common data to return to each request.
   */
  protected $data = [];

  /**
   * @var array Middleware
   */
  protected $mw = [];

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

  /**
   * Determine whether to detect user's preferred language
   * @param string $method
   * @return boolean
   */
  public function lang($method) {
    if ($method) {
      return true;
    }
  }

  /**
   * Add middleware
   */
  public function addMiddleware(...$names) {
    foreach ($names as $name) {
      $this->mw[] = $name;
    }
  }

  /**
   * Define the middleware rules to be applied to all methods
   * @return array Names
   */
  public function middleware() {
    return [];
  }

  public function getMiddleware($method) {
    return array_merge($this->mw, $this->middleware(), empty($this->$method) ? [] : $this->$method);
  }

  public function __call($name, $arguments) {
    if (isset($name) && isset($arguments)) {
      throw new Exception('Method not found.', 404);
    }
  }

}
