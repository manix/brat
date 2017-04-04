<?php

namespace Manix\Brat\Utility\Users\Views;

use Manix\Brat\Components\Forms\Form;
use Manix\Brat\Components\Forms\Views\DefaultFormView;
use Manix\Brat\Utility\BootstrapLayout;

class ForgotView extends BootstrapLayout {

  public function body() {
    $this->cacheT8('manix/util/users/common');
    ?>
    <div class="jumbotron text-center">
      <h2><?= $this->t8('forgotPass') ?></h2>
    </div>


    <div class="container" style="max-width: 480px">
      <?php
      if ($this->data === true) {
        $this->onSuccess();
      } else {
        $this->onRequest();
      }
      ?>
    </div>
    <?php
  }

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

}
