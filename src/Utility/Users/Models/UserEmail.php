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

  public function unvalidate() {
    $this->verified = md5(str_shuffle(mt_rand(PHP_INT_MIN, PHP_INT_MAX) . config('project')['name']));
  }

  public function getCode() {
    return $this->isVerified() ? false : $this->verified;
  }

}
