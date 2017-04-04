<?php

namespace Manix\Brat\Utility\Users\Controllers;

use Manix\Brat\Components\Controller;
use Manix\Brat\Components\Forms\Form;
use Manix\Brat\Components\Validation\Ruleset;
use Manix\Brat\Components\Validation\Validator;
use Manix\Brat\Utility\Captcha\CaptchaManager;
use Manix\Brat\Utility\Users\Models\UserEmailGateway;
use Manix\Brat\Utility\Users\Views\ForgotView;
use Project\Models\GatewayFactory;

class Forgot extends Controller {

  public $page = ForgotView::class;
  protected $form;
  protected $captcha;

  public function __construct() {
    $this->captcha = new CaptchaManager();
    $this->cacheT8('manix/util/users/common');
  }

  protected final function getForm() {
    if ($this->form === null) {
      $this->form = $this->constructForm();
    }

    return $this->form;
  }

  protected function constructForm() {
    $form = new Form();
    $form->add('email', 'email');
    $form->add('captcha', 'text');
    $form->add('', 'submit', $this->t8('sendReq'));

    return $form;
  }

  protected function getRules() {
    $rules = new Ruleset();
    $rules->add('email')->required()->email();
    $rules->add('captcha')->required()->callback([$this->captcha, 'validate']);

    return $rules;
  }

  public function get() {
    return [
        'form' => $this->getForm(),
        'captcha' => $this->captcha
    ];
  }

  public function post() {

    $rules = $this->getRules();

    $v = new Validator();

    if ($v->validate($_POST, $rules)) {
      $gf = new GatewayFactory();
      $egate = $gf->get(UserEmailGateway::class);
      $existing = $egate->find($_POST['email'])->first();
      
      if ($existing) {
        
        $this->captcha->expire();
        
        return true;
      } else {
        $v->setError('email', $this->t8('userNotFound'));
      }
    }

    $this->getForm()->fill($_POST)->errors = $v->getErrors();

    return $this->get();
  }

}
