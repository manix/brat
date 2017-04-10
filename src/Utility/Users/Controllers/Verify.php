<?php

namespace Manix\Brat\Utility\Users\Controllers;

use Manix\Brat\Components\Controller;
use Manix\Brat\Utility\Users\Views\VerifyView;

class Verify extends Controller {

  use GatewayFactory;

  public $page = VerifyView::class;

  public function get() {
    return $_GET;
  }

  public function put() {

    $gate = $this->getEmailGateway();
    $email = $gate->find($_POST['email'] ?? null)->first();

    if (!$email) {
      return false;
    }

    if (!$email->validateCode($_POST['code'] ?? null)) {
      return false;
    }

    $email->validate();

    if (!$gate->persist($email)) {
      return false;
    }

    return true;
  }

}
