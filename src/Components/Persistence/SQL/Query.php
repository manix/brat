<?php

namespace Manix\Brat\Components\Persistence\SQL;

use Exception;

abstract class Query {

  public $table = '';
  public $alias;
  protected $joins = [];
  protected $columns;
  protected $where = '';
  public $whereglue = 'AND';
  public $whereglueGroup = '';
  public $group = '';
  public $order = '';
  public $limit = '';
  protected static $aliasIndex = 'a';
  protected static $placeholder = 'a';
  protected $data = [];

  public function __construct($table = null, $alias = null) {
    $this->table = $table;
    $this->alias = $alias ?? $this->getAlias();
  }

  public function data() {
    return $this->data;
  }

  public function columns(...$columns) {
    $this->columns = $columns;
    return $this;
  }

  public function addColumn($column) {
    $this->columns[] = $column;
    return $this;
  }

  public function join($type, $table, $on, $operand = null, $data = null) {
    $this->joins[] = strtoupper($type) . ' JOIN ' . $table . ' ON ' . $this->buildWhereSQL($on, $operand, $data);
    return $this;
  }

  public function where($column, $operand = null, $data = null) {
    $this->where .= ' ' . $this->whereglue . ' ' . $this->buildWhereSQL($column, $operand, $data);
    $this->whereglue = 'AND';
    return $this;
  }

  public function whereGroupStart() {
    $this->where .= ' ' . $this->whereglue . ' (';
    $this->whereglueGroup = $this->whereglue;
    $this->whereglue = '';
    return $this;
  }

  public function whereGroupEnd() {
    $this->where .= ')';
    $this->whereglue = $this->whereglueGroup;
    return $this;
  }

  public function orWhere($column, $operand = null, $data = null) {
    $this->where .= ' ' . $this->whereglue . ' ' . $this->buildWhereSQL($column, $operand, $data);
    $this->whereglue = 'OR';
    return $this;
  }

  public function limit($start, $length) {
    $this->limit = (int)$start . ', ' . (int)$length;
    return $this;
  }

  public function group($column) {
    $this->group = $column;
    return $this;
  }

  abstract public function build();

  protected function buildWhereSQL($column, $operand = null, $data = null) {

    $sql = $column;

    if ($operand === null) {
      return $sql;
    }

    switch ($operand) {
      case '<':
      case '>':
      case '=':
      case '!=':
      case '<>':
      case 'LIKE':
      case 'NOT LIKE':
        $p = $this->getPlaceholder();
        $sql .= ' ' . $operand . ' ' . $this->getPlaceholderExpression($column, $p);
        $this->data[$p] = $data;
        break;

      case 'IS NULL':
      case 'IS NOT NULL':
        $sql .= ' ' . $operand;
        break;

      case 'NOT IN':
      case 'IN':
        // an empty list: no value is in it, every value is not. "IN ()" is a syntax error
        if (is_array($data) && !$data) {
          return $operand === 'IN' ? '0 = 1' : '1 = 1';
        }

        $sql .= ' ' . $operand . ' (';
        if ($data instanceof self) {
          $sql .= $data->build();
          $this->data = array_merge($this->data, $data->data);
        } else {
          foreach ($data as $value) {
            $p = $this->getPlaceholder();
            $sql .= $this->getPlaceholderExpression($column, $p) . ', ';
            $this->data[$p] = $value;
          }

          $sql = substr($sql, 0, -2);
        }

        $sql .= ')';
        break;

      case 'NOT BETWEEN':
      case 'BETWEEN':
        $p1 = $this->getPlaceholder();
        $p2 = $this->getPlaceholder();
        $sql .= ' ' . $operand . ' ' . $this->getPlaceholderExpression($column, $p1) . ' AND ' . $this->getPlaceholderExpression($column, $p2);
        $this->data[$p1] = $data[0];
        $this->data[$p2] = $data[1];
        break;

      case 'MATCH':
      case 'MATCH IN NATURAL LANGUAGE MODE':
      case 'MATCH WITH QUERY EXPANSION':
      case 'MATCH IN BOOLEAN MODE':
        $p = $this->getPlaceholder($column);
        if (!is_array($column)) {
          $column = [$column];
        }
        $mode = substr($operand, 5);
        $sql = ' MATCH (' . join(',', $column) . ') AGAINST (' . $this->getPlaceholderExpression($column, $p) . $mode . ') ';
        $this->data[$p] = $data;
        break;

      default:
        throw new Exception('Unsupported operand type [' . $operand . '] in where clause.', 500);
    }

    return $sql;
  }

  public static function getPlaceholder() {
    return ':_' . self::$placeholder++;
  }

  public static function getPlaceholderExpression($column, $placeholder) {
    return $placeholder;
  }

  // the character sets whose names are only letters: MySQL and MariaDB read _gbk, _ascii… as a charset introducer,
  // not as an alias, so the aliases skip them
  const CHARSETS = ['ascii' => 1, 'binary' => 1, 'eucjpms' => 1, 'euckr' => 1, 'gbk' => 1, 'greek' => 1,
      'hebrew' => 1, 'macce' => 1, 'macroman' => 1, 'sjis' => 1, 'ujis' => 1];

  public static function getAlias() {
    do {
      $alias = self::$aliasIndex++;
    } while (isset(self::CHARSETS[$alias]));

    return '_' . $alias;
  }

  protected function getJoinClause() {
    return ' ' . implode(' ', $this->joins);
  }

  protected function getWhereClause() {
    if ($this->where) {
      return ' WHERE ' . substr($this->where, 4);
    }
  }

  protected function getGroupClause() {
    if ($this->group) {
      return ' GROUP BY ' . $this->group;
    }
  }

  protected function getOrderClause() {
    if ($this->order) {
      return ' ORDER BY ' . $this->order;
    }
  }

  protected function getLimitClause() {
    if ($this->limit) {
      return ' LIMIT ' . $this->limit;
    }
  }

}
