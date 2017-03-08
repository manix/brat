<?php

namespace Manix\Brat\Components\Persistence\SQL\Queries;

use Manix\Brat\Components\Persistence\SQL\Query;

class UpdateQuery extends Query {

    protected $updates = [];

    public function build() {
        $sql = 'UPDATE ' . $this->table . ' ' . $this->alias . ' SET ';

        foreach ($this->updates as $column => $placeholder) {
            $sql .= $column . ' = ' . $placeholder . ', ';
        }

        $sql = substr($sql, 0, -2);

        return $sql .
        $this->getJoinClause() .
        $this->getWhereClause() .
        $this->getGroupClause() .
        $this->getOrderClause() .
        $this->getLimitClause();
    }

    public function update($data) {
        foreach ($data as $column => $value) {
            if (is_numeric($column)) {
                $eqpos = strpos($value, '=');
                $col = substr($value, 0, $eqpos);
                $val = substr($value, $eqpos + 1);
                $this->updates[$col] = $val;
            } else {
                $p = $this->getPlaceholder();
                $this->updates[$column] = $p;
                $this->data[$p] = $value;
            }
        }

        return $this;
    }

}
