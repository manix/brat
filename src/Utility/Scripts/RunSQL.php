<?php

namespace Manix\Brat\Utility\Scripts;

use Manix\Brat\Components\Validation\Ruleset;
use PDO;
use PDOException;

class RunSQL extends ScriptController {

  /**
   * Runs the files in the given order on one connection. Every statement's error is checked, the
   * first one stops the run (exit code 1) and names its file and statement; the files before it
   * stay applied.
   */
  public function run(...$args) {
    // the data source is the last argument when it isn't a file
    $key = 0;
    if (count($args) > 1 && !is_file(end($args))) {
      $key = array_pop($args);
    }

    // nothing runs when an argument is wrong
    if (!isset($_ENV['data-sources'][$key])) {
      echo 'Not a file nor a data source: ', $key, PHP_EOL;
      exit(1);
    }

    foreach ($args as $path) {
      if (!is_file($path)) {
        echo 'File not found: ', $path, PHP_EOL;
        exit(1);
      }
    }

    $pdo = new PDO(...$_ENV['data-sources'][$key]);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // a file is sent at once and the server splits its statements, which needs emulated prepares
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);

    foreach ($args as $path) {
      // exec() reports only the first statement's error: nextRowset() moves to each next statement and raises its error
      $statement = 1;
      try {
        $stmt = $pdo->query(file_get_contents($path));
        $statement++;
        while ($stmt->nextRowset()) {
          $statement++;
        }
      } catch (PDOException $e) {
        echo $path, ': statement ', $statement, ' failed: ', $e->getMessage(), PHP_EOL;
        exit(1);
      }

      echo $path, ': ', $statement - 1, ' statements', PHP_EOL;
    }

    return 'Migration complete';
  }

  public function argumentRules(Ruleset $rules): Ruleset {
    $rules->add(0)->required()->callback(function($value) {
      return is_file($value) ? null : 'File not found';
    });

    return $rules;
  }

  public function description() {
    return 'Executes the sql statements of one or more files.';
  }

  public function help($name) {
    return <<<HELP

Usage: "{$name} <file> [<file> ...] [<data source>]"

Executes the files' sql statements in the given order, using the connection defined in project/.env.php
under "data-sources" (key 0 unless given). Stops at the first statement that fails, with exit code 1:
the files before it stay applied. For example "{$name} migrations/00000{31..38}-*.sql".

HELP;
  }

}
