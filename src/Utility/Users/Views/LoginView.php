<?php

namespace Manix\Brat\Utility\Users\Views;

use Manix\Brat\Components\Forms\Form;
use Manix\Brat\Helpers\FormViews\DefaultFormView;
use Manix\Brat\Utility\Users\Controllers\Forgot;
use Manix\Brat\Utility\Users\Controllers\Register;
use function route;

class LoginView extends GuestFrame {

  protected function getFormView(Form $form) {
    $view = new DefaultFormView($form, $this->html);
    $view->labels = [
        'email' => $this->t8('email'),
        'password' => $this->t8('password'),
    ];

    $view->setCustomRenderer('remember', function($input) {
      $input->setAttribute('class', 'd-none');
      ?>
      <div class="form-group" data-toggle="buttons">
        <label class="btn btn-outline-primary btn-block">
          <?= $input->toHTML($this->html) ?>
          <span class="mx-2"><?= $this->t8('rememberMe') ?></span>
        </label>
      </div>
      <?php
    });

    $view->setCustomRenderer('login', function($input) {
      ?>
      <div class="form-group d-flex justify-content-between">
        <a href="<?= route(Forgot::class) ?>">
          <?= $this->t8('forgotPass') ?>
        </a>
        <?= $input->setAttribute('class', 'btn btn-secondary')->toHTML($this->html) ?>
      </div>
      <?php
    });

    return $view;
  }

  public function frame() {
    echo $this->getFormView($this->data['form']);

    if (isset($this->data['social']['providers']) && is_array($this->data['social']['providers'])):
      ?>
      <hr/>
      
      <div class="social-login text-center">
        <?php foreach ($this->data['social']['providers'] as $class => $data): ?>
          <a class="btn btn-light btn-lg" href="<?= route($class) ?>">
            <i class="<?= strtolower($class::icon()) ?>"></i>
          </a>
        <?php endforeach; ?>
      </div>
      <?php
    endif;
  }

  public function heading() {
    ?>
    <h2><?= $this->t8('login') ?></h2>
    <a href="<?= route(Register::class) ?>">
      <?= $this->t8('register') ?>
    </a>
    <?php
  }

}
