<?php

namespace Manix\Brat\Utility\Users\Models;

use Manix\Brat\Components\Model;

class UserToken extends Model {

  protected $hash;
  protected $ua;

  public function __set($name, $value) {
    switch ($name) {
      case 'hash':
        $this->setHash($value);
        break;
      
      case 'ua':
        $this->setUA($value);
        break;
      
      default:
        $this->$name = $value;
        break;
    }
  }

  public function __get($name) {
    switch ($name) {
      case 'hash':
        return $this->hash;
        
      case 'ua':
        return $this->ua;
    }
  }

  public function setUA($ua) {
    $this->ua = md5($ua);
  }
  
  public function validateUA($ua) {
    return $this->ua === md5($ua);
  }
  
  public function setHash($hash) {
    $this->hash = password_hash($hash, PASSWORD_BCRYPT);
  }

  public function validateHash($hash) {
    return password_verify($hash, $this->hash);
  }
}
