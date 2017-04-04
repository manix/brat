<?php

namespace Manix\Brat\Utility\Users\Views;

use Manix\Brat\Components\Forms\Form;
use Manix\Brat\Components\Forms\Views\DefaultFormView;
use Manix\Brat\Utility\BootstrapLayout;
use Manix\Brat\Utility\Users\Controllers\Forgot;
use Manix\Brat\Utility\Users\Controllers\Register;
use function route;

class LoginView extends BootstrapLayout {

  public function body() {
    ?>

    <div class="jumbotron text-center">
      <h2><?= $this->t8('manix/util/users/common', 'login') ?></h2>
      <a href="<?= route(Register::class) ?>">
        <?= $this->t8('manix/util/users/common', 'register') ?>
      </a>
    </div>

    <div class="container" style="max-width: 480px">
      <?= $this->getFormView($this->data) ?>
    </div>

    <?php
  }

  protected function getFormView(Form $form) {
    $view = new DefaultFormView($form, $this->html);

    $view->setCustomRenderer('login', function($input) {
      ?>
      <div class="form-group d-flex justify-content-between">
        <a href="<?= route(Forgot::class) ?>">
          <?= $this->t8('manix/util/users/common', 'forgotPass') ?>
        </a>
        <?= $input->setAttribute('class', 'btn btn-secondary')->toHTML($this->html) ?>
      </div>
      <?php
    });

    return $view;
  }

}
