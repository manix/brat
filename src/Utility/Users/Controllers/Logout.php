<?php

namespace Manix\Brat\Utility\Users\Controllers;

use Manix\Brat\Components\Forms\Form;
use Manix\Brat\Helpers\URL;
use Manix\Brat\Utility\HTTP\HTTPController;
use Manix\Brat\Utility\Users\Models\Auth;
use Manix\Brat\Utility\Users\Views\LogoutView;
use const SITE_URL;

class Logout extends HTTPController {

  public $page = LogoutView::class;

  public function get() {
    $form = new Form();
    $form->setMethod('DELETE');

    return $form;
  }

  public function delete() {
    Auth::destroy();

    $url = $_GET['b'] ?? '';
    header('Location: ' . ((new URL($url))->isLocal() ? $url : SITE_URL));
  }

}
