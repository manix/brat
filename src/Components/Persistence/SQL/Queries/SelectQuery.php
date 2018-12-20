<?php

namespace Manix\Brat\Components\Persistence\SQL\Queries;

use Manix\Brat\Components\Persistence\SQL\Query;

class SelectQuery extends Query {

  protected $columns = ['*'];

  public function build() {
    return
    $this->getSelectClause() .
    $this->getJoinClause() .
    $this->getWhereClause() .
    $this->getGroupClause() .
    $this->getOrderClause() .
    $this->getLimitClause();
  }

  public function getSelectClause() {
    $columns = $this->columns;

    if ($this->table instanceof self) {
      $this->data = array_merge($this->data, $this->table->data());

      $table = '(' . $this->table->build() . ') ';
    } else {
      $table = $this->table;
    }

    return 'SELECT ' . join(',', $columns) . ' FROM ' . $table . ' AS `' . $this->alias . '`';
  }

}
