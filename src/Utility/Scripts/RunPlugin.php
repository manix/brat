<?php

namespace Manix\Brat\Utility\Scripts;

use Manix\Brat\Components\Plugin;
use Manix\Brat\Components\Validation\Ruleset;

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

    $method = $format[$command];

    // IMPORTANT: use get_class instead of $class to avoid issues with backslashes
    if ($method === 'install' && in_array(get_class($plugin), config('plugins'))) {
      return 'Plugin already installed.';
    }

    $plugin->$method();

    return 'Plugin ' . $method . 'ed.';
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
