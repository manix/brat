<?php

namespace Manix\Brat\Utility\Users\Views;

use Manix\Brat\Components\Forms\Form;
use Manix\Brat\Helpers\FormViews\DefaultFormView;
use Manix\Brat\Utility\Users\Controllers\Login;
use function route;

class RegisterView extends GuestFrame {

  protected function getFormView(Form $form) {
    $view = new DefaultFormView($form, $this->html);
    $view->labels = [
        'email' => $this->t8('email'),
        'password' => $this->t8('password'),
        'name' => $this->t8('name'),
    ];

    $view->setCustomRenderer('captcha', function($input) use($form) {
      $error = $form->errors[$input->name] ?? null;
      ?>
      <div class="form-group <?= $error ? 'has-danger' : null ?>">
        <div class="text-center">
          <?=
          $this->data['captcha']->generateImageTag($this->html, [
              'class' => 'img-responsive'
          ])
          ?>
        </div>
        <label><?= $this->t8('fillCaptcha') ?></label>
        <?php if ($error): ?>
          <span class="text-danger pull-right">
            <i class="fa fa-exclamation-circle"></i>
            <?= $error ?>
          </span>
        <?php endif; ?>
        <?=
        $input->setAttribute('class', 'form-control')
        ->setAttribute('autocomplete', 'off')
        ->toHTML($this->html)
        ?>
      </div>
      <?php
    });

    return $view;
  }

  public function frame() {
    echo $this->getFormView($this->data['form']);
  }

  public function heading() {
    ?>
    <h2><?= $this->t8('register') ?></h2>
    <a href="<?= route(Login::class) ?>">
      <?= $this->t8('login') ?>
    </a>
    <?php
  }

}
