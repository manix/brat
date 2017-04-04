<?php

namespace Manix\Brat\Utility\Users\Controllers;

use Manix\Brat\Components\Controller;
use Manix\Brat\Components\Forms\Form;
use Manix\Brat\Components\Validation\Ruleset;
use Manix\Brat\Components\Validation\Validator;
use Manix\Brat\Utility\Users\Models\Auth;
use Manix\Brat\Utility\Users\Models\UserEmailGateway;
use Manix\Brat\Utility\Users\Models\UserGateway;
use Manix\Brat\Utility\Users\Views\LoginSuccessView;
use Manix\Brat\Utility\Users\Views\LoginView;
use Project\Models\GatewayFactory;
use function cache;
use function route;

class Login extends Controller {

  public $page = LoginView::class;
  protected $form;
  protected $backto;

  public function __construct($backto = null) {
    if ($backto === null) {
      $backto = $_GET['b'] ?? null;
    }

    $this->backto = $backto;
  }

  protected final function getForm() {
    if ($this->form === null) {
      $this->form = $this->constructForm();
    }

    return $this->form;
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
   * Constructs the login form.
   * @return Form
   */
  protected function constructForm() {
    $form = new Form();
    $form->setAction(route(Login::class) . '?b=' . urlencode($this->backto));
    $form->add('email', 'email');
    $form->add('password', 'password');
    $form->add('login', 'submit', $this->t8('manix/util/users/common', 'login'));

    return $form;
  }

  /**
   * Constructs the rules for the login form.
   * @return Ruleset
   */
  protected function getRules() {
    $rules = new Ruleset();
    $rules->add('email')->required()->email();
    $rules->add('password')->required();

    return $rules;
  }

  public function get() {
    return $this->getForm();
  }

  public function post() {

    $rules = $this->getRules();

    $v = new Validator();

    if ($v->validate($_POST, $rules)) {
      $gf = new GatewayFactory();
      $egate = $gf->get(UserEmailGateway::class);

      $existing = $egate->find($_POST['email'])->first();

      if ($existing) {

        $ugate = $gf->get(UserGateway::class);
        $user = $ugate->find($existing->user_id)->first();

        $key = 'loginblock/' . md5($existing->email . $_SERVER['REMOTE_ADDR']);
        $attempts = cache($key);

        if ($attempts >= $this->allowedAttempts()) {
          $v->setError('email', $this->t8('manix/util/users/common', 'loginBlocked'));
        } else {
          if ($user && password_verify($_POST['password'], $user->password)) {
            Auth::register($user);

            $this->page = $this->getSuccessView();

            cache()->wipe($key);

            return [
                'success' => true,
                'backto' => $_GET['b'] ?? null
            ];
          } else {
            cache($key, $attempts + 1, $this->blockFor());

            $v->setError('password', $this->t8('manix/util/users/common', 'wrongPass'));
          }
        }
      } else {
        $v->setError('email', $this->t8('manix/util/users/common', 'userNotFound'));
      }
    }

    $this->getForm()->fill($_POST)->errors = $v->getErrors();

    return $this->get();
  }

}
