<?php

namespace Manix\Brat\Utility\Users\Models;

use Manix\Brat\Components\Model;

class User extends Model {

  public function setPassword($password) {
    $this->password = password_hash($password, PASSWORD_BCRYPT);
  }

  public function validatePassword($password) {
    return password_verify($password, $this->password);
  }

}
