<?php

namespace Manix\Brat\Utility\Users\Controllers;

use Manix\Brat\Components\Forms\Form;
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
    
    header('Location: ' . ($_GET['b'] ? $_GET['b'] : SITE_URL));
  }
}
