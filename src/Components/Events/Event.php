<?php

namespace Manix\Brat\Components\Events;

class Event {
  
  protected $propagate = true;
  
  public function propagates() {
    return $this->propagate;
  }
  
  public function stopPropagation() {
    $this->propagate = false;
  }
  
}
