<?php

namespace Manix\Brat\Utility\Scripts;

use Manix\Brat\Components\Filesystem\File;
use const PROJECT_PATH;

class RunSetup extends ScriptController {

  public function run(...$args) {
    $env = new File(PROJECT_PATH . '/.env.example.php');

    $env->copy(PROJECT_PATH . '/.env.php');
  }

  public function description() {
    return 'Prepares a new brat project for use.';
  }

  public function help($name) {
    return <<<HELP
    
Usage: "{$name}"

Must be called after installing a new brat project in order to set up the environment.
    
HELP;
  }

}

