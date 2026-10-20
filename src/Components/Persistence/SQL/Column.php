<?php

namespace Manix\Brat\Components\Persistence\SQL;

/**
 * A column of a custom join rule (a joiner's $local and $remote): put into the rule as it is, where every other
 * value is quoted. An object, so no value from the request can pass for one.
 */
class Column {

  protected $sql;

  public function __construct($alias, $field) {
    $this->sql = "`$alias`.`$field`";
  }

  public function __toString() {
    return $this->sql;
  }

}
