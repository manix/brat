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

    return email($email->email, $subject, $this->getActivationMailView($email));
  }

  protected function getActivationMailView(UserEmail $email) {
    return new VerifyMail($email, new HTMLGenerator);
  }

  public function sendForgottenPassMail(UserEmail $email, $code) {
    if (!$email->isVerified()) {
      return false;
    }

    $subject = $this->t8('manix/util/users/emails', 'forgotPass');

    return email($email->email, $subject, $this->getForgottenPassMailView($email->user_id, $code));
  }

  protected function getForgottenPassMailView($userId, $code) {
    return new ForgotMail([
        'id' => $userId,
        'code' => $code
    ], new HTMLGenerator());
  }

}
