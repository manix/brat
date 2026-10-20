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

        if ($rule === 'callback' || $rule === 'or') {
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

  // a string of only spaces is missing too, "0" and 0 are not
  public function required($v) {
    if (is_string($v) ? trim($v) === '' : !is_numeric($v) && empty($v)) {
      return true;
    }
  }

  public function email($v, $d) {
    if (filter_var($v, FILTER_VALIDATE_EMAIL)) {
      if (!$d || getmxrr(substr($v, strpos($v, '@') + 1), $bullshit)) {
        return;
      }
    }
    return true;
  }

  public function length($v, $d) {
    list($min, $max) = $d;
    $length = mb_strlen(trim($v), 'UTF-8');

    if (($min > 0 && $length < $min) || ($max > 0 && $length > $max)) {
      return true;
    }
  }

  public function in($v, array $d) {
    if (!in_array($v, $d)) {
      return true;
    }
  }

  public function inX($v, array $d) {
    if (in_array($v, $d)) {
      return true;
    }
  }

  // checked as digits first: checkdate() throws on anything else
  public function date($v) {
    if (!is_string($v) || !preg_match('/^(\d+)-(\d{1,2})-(\d{1,2})$/', $v, $date) || !checkdate($date[2], $date[3], $date[1])) {
      return true;
    }
  }

  public function datetime($v) {
    try {
      $date = new DateTime($v);
    } catch (Exception $ex) {
      return true;
    }
  }

  public function alphanumeric($v) {
    if (!preg_match('/^[\pL\d]+$/u', $v)) {
      return true;
    }
  }

  public function alphanumericX($v, $d) {
    if (!preg_match('/^[\pL\d' . str_replace('/', '\/', $d) . ']+$/u', $v)) {
      return true;
    }
  }

  public function alphabetic($v) {
    if (!preg_match('/^\pL+$/u', $v)) {
      return true;
    }
  }

  public function alphabeticX($v, $d) {
    if (!preg_match('/^[\pL' . str_replace('/', '\/', $d) . ']+$/u', $v)) {
      return true;
    }
  }

  public function numeric($v) {
    if (!is_numeric($v)) {
      return true;
    }
  }

  public function regex($v, $d) {
    list($regex, $modifiers) = $d;

    if (!preg_match('/' . $regex . '/' . $modifiers, $v)) {
      return true;
    }
  }

  public function url($v) {
    if (!filter_var($v, FILTER_VALIDATE_URL)) {
      return true;
    }
  }

  public function equals($v, $d) {
    if ($v !== $d) {
      return true;
    }
  }

  public function differs($v, $d) {
    if ($v === $d) {
      return true;
    }
  }

  // numeric bounds compare as numbers, request values (strings) included, else both are dates: strtotime() reads
  // most numbers as false, and false passed every comparison
  public function between($v, $d) {
    list($min, $max) = $d;

    if (is_numeric($min) && is_numeric($max)) {
      if (!is_numeric($v) || $v < $min || $v > $max) {
        return true;
      }
    } else {
      $time = is_string($v) ? strtotime($v) : false;

      if ($time === false || $time < strtotime($min) || $time > strtotime($max)) {
        return true;
      }
    }
  }

  public function betweenX($v, $d) {
    list($min, $max) = $d;

    if (is_numeric($min) && is_numeric($max)) {
      if (!is_numeric($v) || $v <= $min || $v >= $max) {
        return true;
      }
    } else {
      $time = is_string($v) ? strtotime($v) : false;

      if ($time === false || $time <= strtotime($min) || $time >= strtotime($max)) {
        return true;
      }
    }
  }

  public function callback($v, callable $d) {
    return $d($v, $this);
  }

  public function file($v, $d = null) {
    // TODO: fix
    // problem: file is always required because $v is array and not empty

    if (!(
    isset($v['name']) &&
    isset($v['type']) &&
    isset($v['tmp_name']) &&
    isset($v['error']) &&
    isset($v['size']) &&
    // a file PHP received in this request: validating anything but $_FILES, tmp_name could name any file
    is_string($v['tmp_name']) && is_uploaded_file($v['tmp_name'])
    )) {
      return 'fileUpload';
    } elseif (isset($d) && $d < ($v['size'] / 1048576)) {
      return 'fileMaxSize';
    }
  }

  public function subset($v, $ruleset, $k) {
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

  public function collection($v, $rulerecord, $k) {
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

  public function version($v) {
    try {
      new Version($v);
    } catch (Exception $ex) {
      return true;
    }
  }

  public function or($v, $ruleset, $k) {
    $validator = new self;

    $records = $ruleset->getAll();
    $data = [];

    foreach ($records as $key => $value) {
      $data[$key] = &$v;
    }

    $validator->validate($data, $ruleset);

    if (count($validator->errors) < count($records)) {
      return;
    }
    
    // the first alternative's message, whatever its key: null would count as passed
    return reset($validator->errors) ?: true;
  }
}
