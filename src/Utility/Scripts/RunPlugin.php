<?php

namespace Manix\Brat\Utility\Scripts;

use Exception;
use Manix\Brat\Components\Validation\Ruleset;
use PDO;
use Manix\Brat\Components\Plugin;

class RunPlugin extends ScriptController {

  public function run(...$args) {
    list($command, $class) = $args;

    $plugin = new $class;

    $format = [
      'install' => 'install',
      'i' => 'install',
      'uninstall' => 'uninstall',
      'u' => 'uninstall'
    ];

    $plugin->{$format[$command]}();
  }

  public function argumentRules(Ruleset $rules): Ruleset {
    $rules->add(0)->required()->in(['install', 'uninstall', 'i', 'u']);
    $rules->add(1)->required()->callback(function($class) {
      if (!is_subclass_of($class, Plugin::class)) {
        return 'Class must inherit from Manix\Brat\Components\Plugin in order to be accepted as a plugin installer.';
      }
    });

    return $rules;
  }

  public function description() {
    return 'Manages plugins.';
  }

  public function help($name) {
    return <<<HELP

Usage: "{$name} <command> <fqcn>"

Runs the <command> method on the supplied class. <command> must be "install" or "uninstall".

HELP;
  }

}
