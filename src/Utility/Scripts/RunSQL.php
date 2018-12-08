<?php

namespace Manix\Brat\Utility\Scripts;

use Exception;
use Manix\Brat\Components\Validation\Ruleset;
use PDO;

class RunSQL extends ScriptController {

  public function run(...$args) {
    $path = $args[0];
    $source = $_ENV['data-sources'][$args[1] ?? 0];

    try {
      $pdo = new PDO(...$source);

      $pdo->exec(file_get_contents($path));

      return 'Migration complete';
    } catch (Exception $e) {
      return $e;
    }
  }

  public function argumentRules(Ruleset $rules): Ruleset {
    $rules->add(0)->required()->callback(function($value) {
      return is_file($value) ? null : 'File not found';
    });

    return $rules;
  }

  public function description() {
    return 'Executes an sql statement from a file.';
  }

  public function help($name) {
    return <<<HELP

Usage: "{$name} <file>"

Executes an sql statement from a file using the connection defined in project/.env.php under key "db".

HELP;
  }

}
