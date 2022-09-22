<?php

namespace Manix\Brat\Utility\Users\Controllers;

use Exception;
use Manix\Brat\Components\Forms\Form;
use Manix\Brat\Components\Model;
use Manix\Brat\Components\Validation\Ruleset;
use Manix\Brat\Components\Validation\Validator;
use Manix\Brat\Helpers\FormController;
use Manix\Brat\Helpers\Redirect;
use Manix\Brat\Utility\Users\Models\Auth;
use Manix\Brat\Utility\Users\Models\User;
use Project\Traits\Users\UserGatewayFactory;
use Manix\Brat\Utility\Users\Views\LoginSuccessView;
use Manix\Brat\Utility\Users\Views\LoginView;
use function cache;
use function config;
use function route;
use function url;

class Login extends FormController {

  use UserGatewayFactory;

  public $page = LoginView::class;
  protected $backto;

  public function before($method) {
    $this->backto = $_SESSION['backto'] = $_GET['b'] ?? url();
    $this->cacheT8('manix/util/users/common');

    return parent::before($method);
  }

  /**
   * Must return the FQCN for the view to display on successful login.
   * @return string FQCN
   */
  protected function getSuccessView() {
    return LoginSuccessView::class;
  }

  /**
   * Specify how many attempts should be allowed before the user's account gets
   * blocked.
   * @return int
   */
  protected function allowedAttempts(): int {
    return 5;
  }

  /**
   * Specify how long the user's account should be blocked for after exceeding
   * the allowed amount of unsuccessful logins.
   * @return int Time measured in seconds.
   */
  protected function blockFor(): int {
    return 600;
  }

  /**
   * Gets called when a login attempt is made.
   * @param User $user
   * @param Validator $validator
   */
  protected function onLoginAttemptResolved(User $user, Validator $validator) {
    $this->getLoginGateway()->persist(new Model([
        'user_id' => $user->id,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
        'ua' => $_SERVER['HTTP_USER_AGENT'] ?? '-',
        'detail' => array_values($validator->getErrors())
    ]));
  }

  protected function constructForm(Form $form): Form {
    $form->setAction(route(static::class, $this->backto ? [
        'b' => $this->backto
    ] : []));
    $form->add('email', 'email');
    $form->add('password', 'password');
    $form->add('remember', 'checkbox', 1);
    $form->add('login', 'submit', $this->t8('login'));

    return $form;
  }

  public function get() {
    if (Auth::user()) {
      $url = $_SESSION['backto'] ?? $_GET['b'] ?? SITE_URL;
      unset($_SESSION['backto']);
      new Redirect($url);
    }

    try {
      $config = config('social');
    } catch (Exception $e) {
      $config = false;
    }

    return [
        'form' => $this->getForm(),
        'social' => $config
    ];
  }

  public function post() {
    unset($_SESSION['backto']);

    return $this->validate($_POST, function($data, $v) {
      $egate = $this->getEmailGateway();

      $email = $egate->find($data['email'])->first();

      if ($email) {

        $ugate = $this->getUserGateway();
        $user = $ugate->find($email->user_id)->first();

        $key = 'users/loginblock/' . md5($email->email . $_SERVER['REMOTE_ADDR']);
        $attempts = cache($key);

        if ($attempts >= $this->allowedAttempts()) {
          $v->setError('email', $this->t8('loginBlocked'));
        } else if (!$email->isVerified()) {
          $v->setError('email', $this->t8('emailNotVerified'));
        } else {
          if ($user && password_verify($data['password'], $user->password)) {
            Auth::register($user);

            $this->page = $this->getSuccessView();

            cache()->wipe($key);

            if (!empty($_POST['remember'])) {
              Auth::issueRememberToken();
            }

            $this->onLoginAttemptResolved($user, $v);

            return [
                'success' => true,
                'backto' => $_GET['b'] ?? null
            ];
          } else {
            cache($key, $attempts + 1, $this->blockFor());

            $v->setError('password', $this->t8('wrongPass'));
          }
        }
      } else {
        $v->setError('email', $this->t8('userNotFound'));
      }

      return $this->defaultFailAction($data, $v);
    });
  }

  protected function constructRules(Ruleset $rules): Ruleset {
    $rules->add('email')->required()->email();
    $rules->add('password')->required();

    return $rules;
  }

}
