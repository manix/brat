<?php

namespace Manix\Brat\Helpers;

use DateTime;
use DateTimeZone;

class Time extends DateTime {

  public $toStringFormat = 'Y-m-d H:i:s';

  public function __construct($time = null, $object = null) {
    parent::__construct($time, $object ?? new DateTimeZone('UTC'));
  }

  public function __toString() {
    return $this->format($this->toStringFormat);
  }

}
