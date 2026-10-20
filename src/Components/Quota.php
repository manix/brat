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
      self::MINUTE => 60,
      self::HOUR => 3600,
      self::DAY => 86400,
      self::WEEK => 604800,
      self::MONTH => 2630016,
      self::YEAR => 31557600,
  ];

  public static function enforce(self $quota, $amount = 1) {
    // one atomic count: requests at the same time can't all read the same value and pass together
    if ($quota->count($amount) > $quota->threshold) {
      throw new Exception('Quota threshold reached.', 503);
    }
  }

  protected $threshold;
  protected $timeframe;
  /**   
   * Last tested value 
   */
  protected $tested;

  public function __construct($type, $id, $threshold, $timeframe = self::HOUR) {
    $this->threshold = $threshold;
    $this->timeframe = $timeframe;
    $this->key = 'quota/' . $type . '/' . $id . '_' . gmdate($this->timeframe);
  }

  /**
   * Returns true if quota + amount exceeds threshold or false otherwise 
   */
  public function test($amount = 1) {
    $this->tested = $this->retrieve() + $amount;
    return $this->tested > $this->threshold;
  }

  public function getTested() {
    return $this->tested;
  }

  public function retrieve() {
    return cache($this->key);
  }

  /**
   * Adds $amount to the quota and returns the new total.
   */
  public function count($amount = 1) {
    return cache()->increment($this->key, self::CONVERSION_TABLE[$this->timeframe], $amount);
  }

  public function persist($amount) {
    return cache($this->key, $amount, self::CONVERSION_TABLE[$this->timeframe]);
  }

}
