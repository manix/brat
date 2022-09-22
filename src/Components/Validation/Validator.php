<?php

namespace Manix\Brat\Components\Validation;

use DateTime;
use Exception;
use Manix\Brat\Components\Translator;

class Validator {

  use Translator;

  protected $errors = array();

  /**
   * Check whether $data passes the rules in $ruleset or not.
   * @param array $data The traversable to be validated.
   * @param Ruleset $ruleset The set of rules to be applied.
   * @param type $strict Indicate whether rules should be applied to abscent keys.
   * @return type
   */
  public function validate($data, Ruleset $ruleset, $strict = true) {
    $valid = true;

    foreach ($ruleset->getAll() as $key => $record) {
      if (isset($data[$key]) || array_key_exists($key, $data)) {
        $value = $data[$key];
      } elseif ($strict === false) {
        continue;
      } else {
        $value = '';
      }

      $rules = $record->getRules();

      if (!isset($rules['required']) && empty($value) && (string)$value === '') {
        continue;
      }

      foreach ($rules as $rule => $parameters) {
        $error = $this->{$rule}($value, $parameters, $key);
        
        if ($error === null) {
          continue;
        } else {
          $valid = false;
        }

        $custom_message = $record->getMessages()[$rule];
        if ($custom_message) {
          $this->errors[$key] = $custom_message;
          break;
        }

        if ($rule === 'callback') {
          $this->errors[$key] = $error;
          break;
        }

        if ($error === true) {
          $tkey = $rule;
        } else {
          $tkey = $error;
        }

        $this->errors[$key] = $this->t8('manix/validation', $tkey, is_array($parameters) ? $parameters : [$parameters]);
        break;
      }
    }

    return $valid;
  }

  public function clearErrors() {
    $this->errors = array();
  }

  public function hasErrors() {
    return !empty($this->errors);
  }

  public function getErrors($key = null) {
    return $key ? (isset($this->errors[$key]) ? $this->errors[$key] : null) : $this->errors;
  }

  public function setError($key, $message) {
    $this->errors[$key] = $message;
  }

  protected function required($v) {
    if (empty($v) && strlen(trim($v)) === 0) {
      return true;
    }
  }

  protected function email($v, $d) {
    if (filter_var($v, FILTER_VALIDATE_EMAIL)) {
      if (!$d || getmxrr(substr($v, strpos($v, '@') + 1), $bullshit)) {
        return;
      }
    }
    return true;
  }

  protected function length($v, $d) {
    list($min, $max) = $d;
    $length = mb_strlen(trim($v), 'UTF-8');

    if (($min > 0 && $length < $min) || ($max > 0 && $length > $max)) {
      return true;
    }
  }

  protected function in($v, array $d) {
    if (!in_array($v, $d)) {
      return true;
    }
  }

  protected function inX($v, array $d) {
    if (in_array($v, $d)) {
      return true;
    }
  }

  protected function date($v) {
    $date = explode('-', $v);
    if (count($date) !== 3 || !checkdate($date[1], $date[2], $date[0])) {
      return true;
    }
  }

  protected function datetime($v) {
    try {
      $date = new DateTime($v);
    } catch (Exception $ex) {
      return true;
    }
  }

  protected function alphanumeric($v) {
    if (!preg_match('/^[\pL\d]+$/u', $v)) {
      return true;
    }
  }

  protected function alphanumericX($v, $d) {
    if (!preg_match('/^[\pL\d' . str_replace('/', '\/', $d) . ']+$/u', $v)) {
      return true;
    }
  }

  protected function alphabetic($v) {
    if (!preg_match('/^\pL+$/u', $v)) {
      return true;
    }
  }

  protected function alphabeticX($v, $d) {
    if (!preg_match('/^[\pL' . str_replace('/', '\/', $d) . ']+$/u', $v)) {
      return true;
    }
  }

  protected function numeric($v) {
    if (!is_numeric($v)) {
      return true;
    }
  }

  protected function regex($v, $d) {
    list($regex, $modifiers) = $d;

    if (!preg_match('/' . $regex . '/' . $modifiers, $v)) {
      return true;
    }
  }

  protected function url($v) {
    if (!filter_var($v, FILTER_VALIDATE_URL)) {
      return true;
    }
  }

  protected function equals($v, $d) {
    if ($v !== $d) {
      return true;
    }
  }

  protected function differs($v, $d) {
    if ($v === $d) {
      return true;
    }
  }

  protected function between($v, $d) {
    list($min, $max) = $d;
    
    switch (gettype($v)) {
      case 'integer':
      case 'double':
        if ($v < $min || $v > $max) {
          return true;
        }
        break;
      default:
        if (strtotime($v) < strtotime($min) || strtotime($v) > strtotime($max)) {
          return true;
        }
        break;
    }
  }

  protected function betweenX($v, $d) {
    list($min, $max) = $d;

    switch (gettype($v)) {
      case 'integer':
      case 'double':
        if ($v <= $min || $v >= $max) {
          return true;
        }
        break;
      default:
        if (strtotime($v) <= strtotime($min) || strtotime($v) >= strtotime($max)) {
          return true;
        }
        break;
    }
  }

  protected function callback($v, callable $d) {
    return $d($v);
  }

  protected function file($v, $d = null) {
    // TODO: fix
    // problem: file is always required because $v is array and not empty

    if (!(
    isset($v['name']) &&
    isset($v['type']) &&
    isset($v['tmp_name']) &&
    isset($v['error']) &&
    isset($v['size']) &&
    is_file($v['tmp_name'])
    )) {
      return 'fileUpload';
    } elseif (isset($d) && $d < ($v['size'] / 1048576)) {
      return 'fileMaxSize';
    }
  }

  protected function subset($v, $ruleset, $k) {
    if (!is_array($v)) {
      return false;
    }

    $validator = new self;

    $validator->validate($v, $ruleset);

    if ($validator->errors) {
      foreach ($validator->errors as $key => $error) {
        $this->errors[$k . '[' . $key . ']'] = $error;
      }

      return false;
    }
  }

  protected function collection($v, $rulerecord, $k) {
    if (!is_array($v)) {
      return false;
    }

    $error = false;
    $validator = new self;
    $ruleset = new Ruleset;
    $ruleset->add('', $rulerecord);

    foreach ($v as $key => $value) {
      $validator->validate(['' => $value], $ruleset);

      if ($validator->errors) {
        foreach ($validator->errors as $itemkey => $itemerror) {
          $this->errors[$k . '[' . $key . ']' . $itemkey] = $itemerror;
        }

        $validator->clearErrors();
        $error = true;
      }
    }

    if ($error === true) {
      return false;
    }
  }

  protected function version($v) {
    try {
      new Version($v);
    } catch (Exception $ex) {
      return true;
    }
  }

}
