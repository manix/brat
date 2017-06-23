<?php

namespace Manix\Brat\Components\Persistence\SQL;

use Manix\Brat\Components\Criteria;
use Manix\Brat\Components\CriteriaInterpreter;
use Manix\Brat\Components\Persistence\SQL\Query;

class SQLGatewayCriteriaInterpreter extends CriteriaInterpreter {

  public function patch(Query $query, Criteria $criteria) {

    switch ($criteria->glue()) {
      case 'OR':
        $method = 'orWhere';
        break;

      default:
        $method = 'where';
        break;
    }

    foreach ($criteria->rules() as $rule) {
      if ($rule instanceof Criteria) {
        $query->whereGroupStart();
        $this->patch($query, $rule);
        $query->whereGroupEnd();
      } else {
        foreach ($rule as $key => $data) {
          $query->$method($query->alias . '.' . $data[0], $this->$key(...$data), $data[1]);
        }
      }
    }
  }

  protected function eq($data, $value) {
    return '=';
  }

  protected function noteq($data, $value) {
    return '!=';
  }

  protected function gt($data, $value) {
    return '>';
  }

  protected function lt($data, $value) {
    return '<';
  }

  protected function in($data, $list) {
    return 'IN';
  }

  protected function notin($data, $list) {
    return 'NOT IN';
  }

  protected function btw($data, $values) {
    return 'BETWEEN';
  }

  protected function notbtw($data, $values) {
    return 'NOT BETWEEN';
  }

  protected function like($data, $value) {
    return 'LIKE';
  }

  protected function notlike($data, $value) {
    return 'NOT LIKE';
  }

}
