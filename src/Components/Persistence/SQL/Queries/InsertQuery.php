<?php

namespace Manix\Brat\Components\Persistence\SQL\Queries;

use Manix\Brat\Components\Persistence\SQL\Query;

class InsertQuery extends Query {

  protected $dataset = '';
  protected $duplicate = '';
  protected $replace = false;

  public function replace($bool) {
    $this->replace = $bool;
    return $this;
  }

  public function build() {
    $sql = ($this->replace ? 'REPLACE' : 'INSERT') . ' INTO ' . $this->table;

    if (!empty($this->columns)) {
      $sql .= ' (' . join(',', $this->columns) . ') ';
    }

    if ($this->dataset instanceof SelectQuery) {
      $sql .= $this->dataset->build();
      $this->data = array_merge($this->data, $this->dataset->data());
    } else {
      $sql .= ' VALUES ' . substr($this->dataset, 0, -2);
    }

    return $sql . $this->duplicate;
  }

  public function insert(...$datasets) {
    if (isset($datasets[0]) && $datasets[0] instanceof SelectQuery) {
      $this->dataset = $datasets[0];
      return;
    }

    foreach ($datasets as $set) {
      $sql = '(';
      foreach ($set as $value) {
        $p = $this->getPlaceholder();
        $sql .= $p . ', ';
        $this->data[$p] = $value;
      }
      $this->dataset .= substr($sql, 0, -2) . '), ';
    }

    return $this;
  }

  public function onDuplicateKey($param) {
    if ($param instanceof UpdateQuery) {
      $this->duplicate = ' ON DUPLICATE KEY ' . str_replace('UPDATE  SET', 'UPDATE', $param->build());
      $this->data = array_merge($this->data, $param->data());
    } elseif ($param === true) {
      $this->duplicate = ' ON DUPLICATE KEY UPDATE ';
      
      foreach ($this->columns as $col) {
        $this->duplicate .= $col . ' = VALUES(' . $col . '),';
      }
      
      $this->duplicate = substr($this->duplicate, 0, -1);
    } else {
      $this->duplicate = ' ON DUPLICATE KEY ' . $param;
    }
    
    return $this;
  }

}
