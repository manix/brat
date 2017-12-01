<?php

namespace Manix\Brat\Components;

use Exception;
use function cache;

class Quota {

  const MINUTE = 'YmdHi';
  const HOUR = 'YmdH';
  const DAY = 'Ymd';
  const WEEK = 'Y.W';
  const MONTH = 'Ym';
  const YEAR = 'Y';
  const CONVERSION_TABLE = [
      self::MINUTE  => 60,
      self::HOUR    => 3600,
      self::DAY     => 86400,
      self::WEEK    => 604800,
      self::MONTH   => 2630016,
      self::YEAR    => 31557600,
  ];

  public static function enforce(self $quota) {
    $current = $quota->retrieve();
    
    if ($current >= $quota->threshold) {
      throw new Exception('Quota threshold reached.', 503);
    }
    
    $quota->persist($current + 1);
  }

  protected $threshold;
  protected $timeframe;

  public function __construct($type, $id, $threshold, $timeframe = self::HOUR) {
    $this->threshold = $threshold;
    $this->timeframe = $timeframe;
    $this->key = 'quota_' . $type . '_' . $id . '_' . gmdate($this->timeframe);
  }

  public function retrieve() {
    return cache($this->key);
  }
  
  public function persist($amount) {
    return cache($this->key, $amount, self::CONVERSION_TABLE[$this->timeframe]);
  }
}
