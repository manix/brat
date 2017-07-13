<?php

namespace Manix\Brat\Components;

class Sorter {

  const ASC = 0;
  const DESC = 1;

  /**
   * Contains the rules that this sorter defines.
   * @var array
   */
  protected $definitions = [];

  /**
   * Allows to define one sorting definition in the constructor.
   * @param string $field
   * @param string $order 'asc' or 'desc'.
   */
  public function __construct($field = null, $order = 'desc') {
    if ($field !== null) {
      $this->$order($field);
    }
  }

  /**
   * Sort by $field in ascending order.
   * @param string $field
   */
  public function asc($field) {
    $this->definition($field, self::ASC);
  }

  /**
   * Sort by $field in descending order.
   * @param string $field
   */
  public function desc($field) {
    $this->definition($field, self::DESC);
  }

  /**
   * Set or get a sorting definition.
   * @param string $field
   * @param int $order 0 for ascending, 1 for descending, null to retrieve current definition.
   */
  public function definition($field, $order = null) {
    if ($order === null) {
      foreach ($this->definitions as $def) {
        if ($def[0] === $field) {
          return $def[1];
        }
      }
    } else {
      $this->definitions[] = [$field, $order];
    }
  }

  /**
   * Clear the sorter.
   */
  public function reset() {
    $this->definitions = [];
  }

  /**
   * Read the sorting definitions.
   * @return array
   */
  public function definitions() {
    return $this->definitions;
  }

}
