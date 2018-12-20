<?php

namespace Manix\Brat\Utility\Users\Models;

use Manix\Brat\Components\Model;

class User extends Model {

  protected $password;

  public function __set($name, $value) {
    if ($name === 'password') {
      $this->setPassword($value);
    } else {
      $this->$name = $value;
    }
  }

  public function __get($name) {
    if ($name === 'password') {
      return $this->password;
    }
  }

  public function setPassword($password) {
    $this->password = password_hash($password, PASSWORD_BCRYPT);
  }

  public function validatePassword($password) {
    return password_verify($password, $this->password);
  }

  public function getPhotoURL() {
    return SITE_URL . '/assets/images/users/hd/' . $this->id . '?v=' . $this->photo_rev;
  }

  public function getThumbURL() {
    return SITE_URL . '/assets/images/users/thumb/' . $this->id . '?v=' . $this->photo_rev;
  }
}
