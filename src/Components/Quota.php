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
    if ($quota->test($amount)) {
      throw new Exception('Quota threshold reached.', 503);
    } $quota->persist($quota->getTested());
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

  public function persist($amount) {
    return cache($this->key, $amount, self::CONVERSION_TABLE[$this->timeframe]);
  }

}
