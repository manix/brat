<?php

namespace Manix\Brat\Components\Persistence\SQL\Queries;

use Manix\Brat\Components\Persistence\SQL\Query;

class DeleteQuery extends Query {

  public function build() {
    return 'DELETE ' . $this->alias . ' FROM ' . $this->table . ' AS `' . $this->alias . '`' .
    $this->getJoinClause() .
    $this->getWhereClause() .
    $this->getGroupClause() .
    $this->getOrderClause() .
    $this->getLimitClause();
  }

}
