<?php

namespace Manix\Brat\Components;

use Iterator;
use JsonSerializable;

class Collection implements JsonSerializable, Iterator {

  /**
   * @var string Name of interface allowed in this collection.
   */
  protected $interface;

  /**
   * @var array List of objects in this collection.
   */
  protected $list = array();

  /**
   * @return string Name of the interface allowed in this collection.
   */
  public function getInterfaceName() {
    return $this->interface;
  }

  /**
   * A collection of objects sharing a same interface.
   *
   * @param string $interface Name of the interface allowed in this collection.
   * @param array $items Array of objects to add to the collection initially.
   */
  public function __construct($interface, array $items = array()) {
    $this->interface = $interface;
    $this->push(...$items);
  }

  /**
   * Get all items in this collection.
   * @return array All objects contained in this collection.
   */
  public function items() {
    return $this->list;
  }

  /**
   * Get the object at an index.
   * @param int $index
   * @return object The object residing at $index.
   */
  public function item($index) {
    return isset($this->list[$index]) ? $this->list[$index] : null;
  }

  /**
   * Add objects to this collection. If the object does not implement the
   * required interface it will be silently discarded.
   * @param array $items Objects to be added
   * @return $this
   */
  public function push(...$items) {
    foreach ($items as $item) {
      if ($item instanceof $this->interface) {
        $this->list[] = $item;
      }
    }

    return $this;
  }

  /**
   * Remove an object from this collection.
   * @param int $index
   * @return $this
   */
  public function remove($index) {
    unset($this->list[$index]);

    return $this;
  }

  /**
   * Wipe the entire collection, resulting in an empty collection.
   * @return $this
   */
  public function clear() {
    $this->list = [];

    return $this;
  }

  /**
   * Count the objects in this collection.
   * @return int Number of objects in this collection.
   */
  public function count() {
    return count($this->list);
  }

  public function jsonSerialize() {
    return $this->list;
  }

  public function reverse() {
    $this->list = array_reverse($this->list);
    return $this;
  }

  public function rewind() {
    reset($this->list);
  }

  public function current() {
    return current($this->list);
  }

  public function key() {
    return key($this->list);
  }

  public function next() {
    return next($this->list);
  }

  public function valid() {
    $key = key($this->list);
    return ($key !== null && $key !== false);
  }

  /**
   * Modify a property on all objects contained in this collection.
   * @param string $key The property to be modified or a callback that will get passed the entire model.
   * @param mixed $value The new value or null when using a callback.
   */
  public function modify($key, $value = null) {
    if (is_callable($key)) {
      foreach ($this->list as $item) {
        $key($item);
      }
    } else {
      foreach ($this->list as $item) {
        $item->$key = $value;
      }
    }
  }

  /**
   * Get the first object in this collection.
   *
   * WARNING: This method modifies the internal array pointer.
   *
   * @return Object An instance of $this->interface.
   */
  public function first() {
    return reset($this->list);
  }

  /**
   * Get the last object in this collection.
   *
   * WARNING: This method modifies the internal array pointer.
   *
   * @return Object An instance of $this->interface.
   */
  public function last() {
    return end($this->list);
  }

  /**
   * Returns the first model in the list which has a property that matches
   *
   * @param string $property The model property.
   * @param mixed $value Value for comparison
   * @param bool $key Whether to return the index at which the model resides or the model itself.
   * @return mixed Index or model, depending on $key.
   */
  public function find($property, $value, $key = false) {
    foreach ($this->list as $index => $model) {
      if ($model->$property === $value) {
        return $key ? $index : $model;
      }
    }

    return null;
  }

  /**
   * Returns the first model in the list which returns true from the callback
   *
   * @param callable $callback Callback for comparison, will be called with 1 argument - the model.
   * @param bool $key Whether to return the index at which the model resides or the model itself.
   * @return mixed Index or model, depending on $key.
   */
  public function findCallback(callable $callback, $key = false) {
    foreach ($this->list as $index => $model) {
      if ($callback($model)) {
        return $key ? $index : $model;
      }
    }

    return null;
  }

  /**
   * Returns an array with returned values from $func for each element.
   * @param Callable $func
   * @return array
   */
  public function map(Callable $func) {
    $result = [];

    foreach ($this->list as $item) {
      $result[] = $func($item);
    }

    return $result;
  }

  /**
   * Returns a new identical collection with filtered items.
   * @param Callable $func
   * @return \self
   */
  public function filter(Callable $func) {
    $new = new self($this->interface);

    foreach ($this->list as $item) {
      if ($func($item)) {
        $new->push($item);
      }
    }

    return $new;
  }

}
