<?php

namespace Manix\Brat\Components;

use Exception;
use Manix\Brat\Components\Events\EventEmitter;
use Manix\Brat\Components\Events\EventEmitterInterface;
use Manix\Brat\Components\Validation\Ruleset;
use Manix\Brat\Components\Validation\Validator;
use Manix\Brat\Utility\Events\Controllers\AfterExecute;
use Manix\Brat\Utility\Events\Controllers\BeforeExecute;
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
   * Gets called before the program executes a method on the controller.
   * @param string $method The method that is about to get executed.
   * @return string The method that will be executed.
   */
  public function before($method) {
    $v = new Validator();
    
    if (!$v->validate($_GET, $this->query(new Ruleset()))) {
      throw new Exception('Invalid query parameters', 400);
    }
    
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
   * Fetch the method names that must be protected against CSRF attacks.
   * @return array List of method names.
   */
  public function csrf() {
    return ['post', 'put', 'delete'];
  }

  /**
   * Apply rules to the $_GET array
   * @param Ruleset $rules
   * @return Ruleset
   */
  public function query(Ruleset $rules): Ruleset {
    return $rules;
  }

  public function __call($name, $arguments) {
    if (isset($name) && isset($arguments)) {
      throw new Exception('Method not found.', 404);
    }
  }

  public final function execute($method) {
    $method = $this->before($method);
    $this->emit(new BeforeExecute($this, $method));
    $data = $this->after($this->$method());
    $this->emit(new AfterExecute($this, $data));

    return $data;
  }

}
