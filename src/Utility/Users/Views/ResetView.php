<?php

namespace Manix\Brat\Utility\Users\Views;

use Manix\Brat\Components\Forms\Form;
use Manix\Brat\Helpers\FormViews\DefaultFormView;
use Manix\Brat\Helpers\HTMLGenerator;
use Manix\Brat\Utility\Users\Controllers\Login;
use Manix\Brat\Utility\Users\Controllers\Reset;
use Manix\Brat\Utility\Users\Models\User;
use Project\Views\Users\GuestFrame;
use function route;

class ResetView extends GuestFrame {

  public function __construct($data, HTMLGenerator $html) {
    parent::__construct($data, $html);

    $this->cacheT8('manix/util/users/common');
  }

  public function frame() {
    if ($this->data instanceof User) {
      $this->success();
    } elseif ($this->data === false) {
      $this->fail();
    } else {
      $this->form();
    }
  }

  protected function form() {
    $form = new Form();
    $form->setMethod('PUT');
    $form->setAction(route(Reset::class));
    $form->add('id', 'hidden', $this->data['id'] ?? '');
    $form->add('code', 'hidden', $this->data['code'] ?? '');
    $form->add('', 'submit', $this->t8('continue'))->setAttribute('class', 'btn btn-success');

    $view = new DefaultFormView($form, $this->html);
    ?>

    <p><?= $this->t8('clickToResetPass') ?></p>
    <?= $view ?>

    <?php
  }

  protected function success() {
    ?>
    <div class="alert alert-success text-center">
      <span><?= $this->t8('resetSuccess') ?></span>
      <hr/>
      <strong><?= html($this->data->passwordRaw ?? null) ?></strong>
      <hr/>
      <a class="btn btn-success" href="<?= route(Login::class) ?>">
        <?= $this->t8('login') ?>
      </a>
    </div>
    <?php
  }

  protected function fail() {
    ?>
    <div class="alert alert-danger">
      <?= $this->t8('resetFail') ?>
    </div>
    <?php
  }

  public function heading() {
    ?>
    <h2><?= $this->t8('resetPass') ?></h2>
    <?php
  }

}
