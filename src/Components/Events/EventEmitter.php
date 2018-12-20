<?php

namespace Manix\Brat\Components\Events;

trait EventEmitter {

  protected $listeners = [];

  public function on($event, callable $listener) {
    if (!isset($this->listeners[$event])) {
      $this->listeners[$event] = [];
    }
    $this->listeners[$event][] = $listener;
  }

  public function once($event, callable $listener) {
    $onceListener = function (...$args) use (&$onceListener, $event, $listener) {
      $this->removeListener($event, $onceListener);
      $listener(...$args);
    };
    $this->on($event, $onceListener);
  }

  public function removeListener($event, callable $listener) {
    if (isset($this->listeners[$event])) {
      $index = array_search($listener, $this->listeners[$event], true);
      if (false !== $index) {
        unset($this->listeners[$event][$index]);
      }
    }
  }

  public function removeAllListeners($event = null) {
    if ($event !== null) {
      unset($this->listeners[$event]);
    } else {
      $this->listeners = [];
    }
  }

  public function listeners($event) {
    return isset($this->listeners[$event]) ? $this->listeners[$event] : [];
  }

  /**
   * Emit an event.
   * @param Event $event
   * @return Event The event after listeners execution.
   */
  public function emit(Event $event) {
    
    foreach ($this->listeners(get_class($event)) as $listener) {
      $listener($event);

      if (!$event->propagates()) {
        break;
      }
    }
    
    return $event;
  }

}
