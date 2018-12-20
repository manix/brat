<?php

namespace Manix\Brat\Utility\Users\Controllers;

use Manix\Brat\Components\Forms\Form;
use Manix\Brat\Components\Validation\Ruleset;
use Manix\Brat\Helpers\FormController;
use Manix\Brat\Utility\Captcha\CaptchaManager;
use Project\Traits\Users\UserGatewayFactory;
use Manix\Brat\Utility\Users\Views\ForgotView;
use function cache;

class Forgot extends FormController {

  use UserGatewayFactory,
      Mailer;

  public $page = ForgotView::class;
  protected $captcha;

  public function before($method) {
    $this->captcha = new CaptchaManager();
    $this->cacheT8('manix/util/users/common');

    return $method;
  }

  protected function constructForm(Form $form): Form {
    $form->add('email', 'email');
    $form->add('captcha', 'text');
    $form->add('', 'submit', $this->t8('sendReq'));

    return $form;
  }

  protected function constructRules(Ruleset $rules): Ruleset {
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

    return $this->validate($_POST, function($data, $v) {
      $egate = $this->getEmailGateway();
      $email = $egate->find($data['email'])->first();

      if ($email) {
        if (!$email->isVerified()) {
          $v->setError('email', $this->t8('emailNotVerified'));
        } else {

          $this->captcha->expire();

          $code = cache('users/resetpass/' . $email->user_id, function() {
            return md5(random_bytes(10));
          }, 3600);

          return $this->sendForgottenPassMail($email, $code);
        }
      } else {
        $v->setError('email', $this->t8('userNotFound'));
      }

      return $this->defaultFailAction($data, $v);
    });
  }

}
