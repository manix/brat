<?php

namespace Manix\Brat\Utility\Users\Controllers;

use Manix\Brat\Utility\HTTP\HTTPController;
use Project\Traits\Users\UserGatewayFactory;
use Manix\Brat\Utility\Users\Views\VerifyView;

class Verify extends HTTPController {

  use UserGatewayFactory;

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
