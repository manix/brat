<?php

namespace Manix\Brat\Helpers;

use DateTime;
use DateTimeZone;
use JsonSerializable;

class Time extends DateTime implements JsonSerializable {

  public $toStringFormat = 'Y-m-d H:i:s';
  public $toJSONFormat = 'c';

  public function __construct($time = null, $object = null) {
    parent::__construct($time, $object ?? new DateTimeZone('UTC'));
  }

  public function __toString() {
    return $this->format($this->toStringFormat);
  }

  public function jsonSerialize() {
    return $this->format($this->toJSONFormat);
  }

}
