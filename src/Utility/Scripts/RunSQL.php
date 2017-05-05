<?php

namespace Manix\Brat\Utility\Scripts;

use Exception;
use Manix\Brat\Components\Validation\Ruleset;
use PDO;

class RunSQL extends ScriptController {

  public function run(...$args) {
    list($path) = $args;

    $host = $_ENV['db']['host'] ?? null;
    $dbname = $_ENV['db']['dbname'] ?? null;
    $charset = $_ENV['db']['charset'] ?? null;
    $user = $_ENV['db']['user'] ?? null;
    $pass = $_ENV['db']['pass'] ?? null;

    try {
      $pdo = new PDO("mysql:host={$host};dbname={$dbname};charset={$charset};", $user, $pass, [
          PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
      ]);

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
