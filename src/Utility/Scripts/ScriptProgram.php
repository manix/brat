<?php

namespace Manix\Brat\Utility\Scripts;

use Manix\Brat\Components\Controller;
use Manix\Brat\Components\Program;
use Throwable;
use function config;

class ScriptProgram extends Program {

  public function createController(string $route): Controller {
    global $argv;

    $scripts = config('scripts');

    if (!isset($scripts[$route])) {
      exit('Unknown command');
    }

    $args = $argv;

    unset($args[0], $args[1]);

    return new $scripts[$route](array_values($args));
  }

  public function respond($data) {
    print_r($data);
    echo PHP_EOL;
  }

  public function determineRoute(): string {
    global $argv;

    $script = $argv[1] ?? null;

    if (!$script) {
      exit('Not enough arguments, see help for available commands.');
    }

    return $script;
  }

  public function determineMethod(): string {
    return 'exec';
  }

  public function error(Throwable $t) {
    echo $t;
  }

}
