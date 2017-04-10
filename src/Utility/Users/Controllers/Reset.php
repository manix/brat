<?php

namespace Manix\Brat\Utility\Users\Controllers;

use Manix\Brat\Components\Controller;
use Manix\Brat\Utility\Users\Views\ResetView;
use function cache;

class Reset extends Controller {

  use GatewayFactory;

  public $page = ResetView::class;

  public function get() {
    return $_GET;
  }

  public function put() {

    $id = $_POST['id'] ?? null;
    $gate = $this->getUserGateway();
    $user = $gate->find($id)->first();

    if (!$user) {
      return false;
    }

    if (empty($_POST['code']) || $_POST['code'] !== cache('users/resetpass/' . $id)) {
      return false;
    }

    $password = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ123456790!@#$%^&*()_+-=[]{}\\|;:/.,<>?'), 0, 12);

    $user->passwordRaw = $password;
    $user->setPassword($password);

    if (!$gate->persist($user)) {
      return false;
    }

    cache()->wipe('users/resetpass/' . $id);

    return $user;
  }

}
