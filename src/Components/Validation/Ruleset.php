<?php

namespace Manix\Brat\Components\Validation;

class Ruleset {

  /**
   * @var RulesetRecord[]
   */
  protected $set = [];

  /**
   * @return RulesetRecord
   */
  public function add($name, RulesetRecord $record = null) {

    if ($record === null) {
      $record = new RulesetRecord();
    }

    $this->set[$name] = $record;

    return $this->set[$name];
  }

  public function remove($name) {
    unset($this->set[$name]);
  }

  public function has($name) {
    return isset($this->set[$name]);
  }

  /**
   * @return RulesetRecord
   */
  public function get($name) {
    return $this->set[$name] ?? $this->add($name);
  }

  /**
   * 
   * @return RulesetRecord[]

   */
  public function getAll() {
    return $this->set;
  }

  public function clear() {
    $this->set = [];

    return $this;
  }

}

class RulesetRecord {

  protected $rules = [];
  protected $messages = [];

  public function getRules() {
    return $this->rules;
  }

  public function getMessages() {
    return $this->messages;
  }

  public function has($rule) {
    return isset($this->rules[$rule]);
  }

  public function get($rule) {
    return isset($this->rules[$rule]) ? $this->rules[$rule] : null;
  }

  public function removeRule($rule) {
    unset($this->rules[$rule]);
    unset($this->messages[$rule]);
  }

  public function required($message = null) {
    $this->rules['required'] = true;
    $this->messages['required'] = $message;

    return $this;
  }

  public function email($mx = true, $message = null) {
    $this->rules['email'] = $mx;
    $this->messages['email'] = $message;

    return $this;
  }

  public function length($min = 0, $max = 0, $message = null) {
    $this->rules['length'] = [$min, $max];
    $this->messages['length'] = $message;

    return $this;
  }

  public function in(array $list, $message = null) {
    $this->rules['in'] = $list;
    $this->messages['in'] = $message;

    return $this;
  }

  public function inX(array $list, $message = null) {
    $this->rules['inX'] = $list;
    $this->messages['inX'] = $message;

    return $this;
  }

  public function date($message = null) {
    $this->rules['date'] = true;
    $this->messages['date'] = $message;

    return $this;
  }

  public function datetime($message = null) {
    $this->rules['datetime'] = true;
    $this->messages['datetime'] = $message;

    return $this;
  }

  public function alphanumeric($message = null) {
    $this->rules['alphanumeric'] = true;
    $this->messages['alphanumeric'] = $message;

    return $this;
  }

  public function alphanumericX($regex, $message = null) {
    $this->rules['alphanumericX'] = $regex;
    $this->messages['alphanumericX'] = $message;

    return $this;
  }

  public function alphabetic($message = null) {
    $this->rules['alphabetic'] = true;
    $this->messages['alphabetic'] = $message;

    return $this;
  }

  public function alphabeticX($regex, $message = null) {
    $this->rules['alphabeticX'] = $regex;
    $this->messages['alphabeticX'] = $message;

    return $this;
  }

  public function numeric($message = null) {
    $this->rules['numeric'] = true;
    $this->messages['numeric'] = $message;

    return $this;
  }

  public function regex($regex, $modifiers = '', $message = null) {
    $this->rules['regex'] = [str_replace('/', '\/', $regex), $modifiers];
    $this->messages['regex'] = $message;

    return $this;
  }

  public function url($message = null) {
    $this->rules['url'] = true;
    $this->messages['url'] = $message;

    return $this;
  }

  public function equals($value, $message = null) {
    $this->rules['equals'] = $value;
    $this->messages['equals'] = $message;

    return $this;
  }

  public function differs($value, $message = null) {
    $this->rules['differs'] = $value;
    $this->messages['differs'] = $message;

    return $this;
  }

  public function between($a, $b, $message = null) {
    $this->rules['between'] = [$a, $b];
    $this->messages['between'] = $message;

    return $this;
  }

  public function betweenX($a, $b, $message = null) {
    $this->rules['betweenX'] = [$a, $b];
    $this->messages['betweenX'] = $message;

    return $this;
  }

  public function callback(callable $callback) {
    $this->rules['callback'] = $callback;
    $this->messages['callback'] = null;

    return $this;
  }

  /**
   * Validate file input.
   * 
   * @param type $size The maximum file size in MB
   * @return RulesetRecord
   */
  public function file($size = null) {
    $this->rules['file'] = $size;
    $this->messages['file'] = null;

    return $this;
  }

  public function subset(Ruleset $subset = null) {
    if ($subset === null) {
      $subset = new Ruleset();
    }

    $this->rules['subset'] = $subset;
    $this->messages['subset'] = null;

    return $subset;
  }

  public function collection(RulesetRecord $record = null) {
    if ($record === null) {
      $record = new RulesetRecord();
    }

    $this->rules['collection'] = $record;
    $this->messages['collection'] = null;

    return $record;
  }

  public function version($message = null) {
    $this->rules['version'] = true;
    $this->messages['version'] = $message;

    return $this;
  }

  public function or($rulesetorcb, $message = null) {
    if ($rulesetorcb instanceof Ruleset) {
      $ruleset = $rulesetorcb;
    } else {
      $ruleset = $rulesetorcb(new Ruleset);
    }

    $this->rules['or'] = $ruleset;
    $this->messages['or'] = $message;

    return $this;
  }

}
