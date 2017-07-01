<?php

namespace Manix\Brat\Utility\Users\Views;

use Manix\Brat\Components\Forms\Form;
use Manix\Brat\Helpers\FormViews\DefaultFormView;
use Project\Views\Users\GuestFrame;

class ForgotView extends GuestFrame {

  protected function onSuccess() {
    ?>

    <div class="alert alert-success text-center">
      <?= $this->t8('forgotSuccess') ?>
      <hr/>
      <p><?= $this->t8('forgotSuccessExt') ?></p>
    </div>

    <?php
  }

  protected function onRequest() {
    echo $this->getFormView($this->data['form']);
  }

  protected function getFormView(Form $form) {
    $view = new DefaultFormView($form, $this->html);
    $view->labels = [
        'email' => $this->t8('email'),
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
    if ($this->data === true) {
      $this->onSuccess();
    } else {
      $this->onRequest();
    }
  }

  public function heading() {
    ?>
    <h2><?= $this->t8('forgotPass') ?></h2>
    <?php
  }

}
