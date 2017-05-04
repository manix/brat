<?php

namespace Manix\Brat\Utility\Scripts;

use Manix\Brat\Components\Controller;
use PDO;
use Exception;

class Migrate extends Controller {

  public function run() {
    $host = $_ENV['db']['host'] ?? null;
    $dbname = $_ENV['db']['dbname'] ?? null;
    $charset = $_ENV['db']['charset'] ?? null;
    $user = $_ENV['db']['user'] ?? null;
    $pass = $_ENV['db']['pass'] ?? null;


    try {
      $pdo = new PDO("mysql:host={$host};dbname={$dbname};charset={$charset};", $user, $pass, [
          PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
      ]);

      $pdo->exec(file_get_contents('database-schemas'));

      return 'Migration complete';
    } catch (Exception $e) {
      return $e;
    }
  }

}
