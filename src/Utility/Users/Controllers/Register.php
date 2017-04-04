<?php

namespace Manix\Brat\Utility\Users\Controllers;

use Manix\Brat\Components\Controller;
use Manix\Brat\Components\Filesystem\Directory;
use Manix\Brat\Components\Forms\Form;
use Manix\Brat\Components\Model;
use Manix\Brat\Components\Validation\Ruleset;
use Manix\Brat\Components\Validation\Validator;
use Manix\Brat\Utility\Captcha\CaptchaManager;
use Manix\Brat\Utility\Users\Models\UserEmailGateway;
use Manix\Brat\Utility\Users\Models\UserGateway;
use Manix\Brat\Utility\Users\Views\RegisterSuccessView;
use Manix\Brat\Utility\Users\Views\RegisterView;
use const PROJECT_PATH;

class Register extends Controller {

  public $page = RegisterView::class;
  protected $form;
  protected $captcha;

  public function __construct() {
    $this->captcha = new CaptchaManager();
  }

  protected final function getForm() {
    if ($this->form === null) {
      $this->form = $this->constructForm();
    }

    return $this->form;
  }

  /**
   * Construct the register form.
   * @return Form
   */
  protected function constructForm() {
    $form = new Form();
    $form->add('email', 'email');
    $form->add('password', 'password');
    $form->add('name', 'text');
    $form->add('captcha', 'text');
    $form->add('', 'submit', $this->t8('manix/util/users/common', 'register'))
    ->setAttribute('class', 'btn btn-primary');

    return $form;
  }

  /**
   * Define the view to display after successful registration.
   * @return FQCN
   */
  protected function getSuccessView() {
    return RegisterSuccessView::class;
  }

  /**
   * Define the rules for registration.
   * @return Ruleset
   */
  protected function getRules() {
    $rules = new Ruleset();
    $rules->add('email')->required()->email();
    $rules->add('password')->required()->length(8, 255);
    $rules->add('name')->required()->alphabeticX('\' -');
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
      $dir = new Directory(PROJECT_PATH . '/files/data');

      $egate = new UserEmailGateway($dir);

      $existing = $egate->find($_POST['email']);

      if ($existing->count()) {
        $v->setError('email', $this->t8('manix/util/users/common', 'emailTaken'));
      } else {

        $ugate = new UserGateway($dir);
        $user = new Model($_POST);
        $user->password = password_hash($_POST['password'], PASSWORD_BCRYPT);

        $ugate->persist($user);

        $egate->persist(new Model([
            'user_id' => $user->id,
            'email' => $_POST['email']
        ]));

        $this->page = $this->getSuccessView();
        $this->captcha->expire();

        return true;
      }
    }

    $this->getForm()->fill($_POST)->errors = $v->getErrors();

    return $this->get();
  }

}
