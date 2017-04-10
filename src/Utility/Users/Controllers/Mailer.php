<?php

namespace Manix\Brat\Utility\Users\Controllers;

use Manix\Brat\Components\Translator;
use Manix\Brat\Helpers\HTMLGenerator;
use Manix\Brat\Utility\Users\Models\UserEmail;
use Manix\Brat\Utility\Users\Views\Email\ForgotMail;
use Manix\Brat\Utility\Users\Views\Email\VerifyMail;
use function email;

trait Mailer {

  use Translator;

  public function sendActivationMail(UserEmail $email) {
    if ($email->isVerified()) {
      return false;
    }

    $subject = $this->t8('manix/util/users/emails', 'verifySubject');

    return email($email->email, $subject, new VerifyMail($email, new HTMLGenerator()));
  }

  public function sendForgottenPassMail(UserEmail $email, $code) {
    if (!$email->isVerified()) {
      return false;
    }

    $subject = $this->t8('manix/util/users/emails', 'forgotPass');

    return email($email->email, $subject, new ForgotMail([
        'id' => $email->user_id,
        'code' => $code
    ], new HTMLGenerator()));
  }

}
