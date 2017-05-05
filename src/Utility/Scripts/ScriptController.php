<?php

namespace Manix\Brat\Utility\Scripts;

use Manix\Brat\Components\Controller;
use Manix\Brat\Components\Validation\Ruleset;
use Manix\Brat\Components\Validation\Validator;

abstract class ScriptController extends Controller {

  protected $args = [];

  public function __construct(array $args = []) {
    $this->args = $args;
  }

  abstract public function description();

  abstract public function help($command);

  abstract public function run(...$args);

  public function argumentRules(Ruleset $rules): Ruleset {
    return $rules;
  }

  public final function exec() {
    $rules = $this->argumentRules(new Ruleset());

    $validator = new Validator();

    if ($validator->validate($this->args, $rules)) {
      return $this->run(...$this->args);
    } else {
      $resp = 'Invalid arguments provided:' . PHP_EOL;

      foreach ($validator->getErrors() as $key => $value) {
        $resp .= '#' . $key . ': ' . $value . PHP_EOL;
      }

      return $resp;
    }
  }

}
