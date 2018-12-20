<?php

namespace Manix\Brat\Utility\Users\Models;

use Manix\Brat\Components\Model;

class UserEmail extends Model {

  public function isVerified() {
    return $this->verified === 'Y';
  }

  public function validateCode($code) {
    return $this->isVerified() ? false : $this->verified === $code;
  }

  public function validate() {
    $this->verified = 'Y';
  }

  public function invalidate() {
    $this->verified = md5(random_bytes(10));
  }

  public function getCode() {
    return $this->isVerified() ? false : $this->verified;
  }

  public function __toString() {
    return $this->email;
  }

}
