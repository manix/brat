<?php

namespace Manix\Brat\Utility\Users\Scripts;

use Manix\Brat\Components\Criteria;
use Manix\Brat\Components\Validation\Ruleset;
use Manix\Brat\Helpers\Time;
use Manix\Brat\Utility\Scripts\ScriptController;
use Project\Traits\Users\UserGatewayFactory;

class Users extends ScriptController {
  
  use UserGatewayFactory;

  public function argumentRules(Ruleset $rules): Ruleset {
    return $this->addActionRules($this->args[0] ?? null, $rules);
  }

  protected function addActionRules($action, Ruleset $rules): Ruleset {
    switch ($action) {
      case 'cleartokens':
        $rules->add(1)->required()->numeric();
        break;
      
      case 'clearlogins':
        $rules->add(1)->required()->numeric();
        break;

      default:
        break;
    }

    return $rules;
  }

  public function description() {
    return 'Manage users';
  }

  public function help($name) {
    return <<<HELP
    
Usage: "{$name} <action> [action options]"

Actions:
  clearlogins <days: int> - Delete login records that were created <days> days ago or earlier.
  cleartokens <days: int> - Delete token records that have not been used to log in for [days] days.
    
HELP;
  }

  public function run(...$args) {
    return $this->{'run_' . $args[0]}(...array_slice($args, 1));
  }
  
  public function run_clearlogins($days) {
    $date = new Time();
    $date->setTimestamp(time() - $days * 24 * 3600);
    
    $gate = $this->getLoginGateway();
    $criteria = new Criteria;
    $criteria->less('created', $date);
    
    return $gate->wipeBy($criteria) ? 'Login wipe completed' : 'Nothing was wiped';
  }

  public function run_cleartokens($days) {
    $date = new Time();
    $date->setTimestamp(time() - $days * 24 * 3600);
    
    $tgate = $this->getTokenGateway();
    $lgate = $this->getLoginGateway();
    $sql = <<<SQL
DELETE t
FROM
  {$tgate->getTable()} t
LEFT OUTER JOIN {$lgate->getTable()} l ON l.detail = CONCAT('{"t":', t.id, '}')
WHERE
	l.created < ?
OR l.created IS NULL
SQL;

    $stmt = $tgate->getPDO()->prepare($sql);
    $stmt->execute([$date]);

    return $stmt->rowCount() . " tokens deleted.";
  }

}
