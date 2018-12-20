<?php

namespace Manix\Brat\Utility\Users\Views;

use Manix\Brat\Components\Forms\Form;
use Manix\Brat\Helpers\FormViews\DefaultFormView;
use Manix\Brat\Helpers\HTMLGenerator;
use Manix\Brat\Utility\Users\Controllers\Login;
use Manix\Brat\Utility\Users\Controllers\Verify;
use Project\Views\Users\GuestFrame;
use function route;

class VerifyView extends GuestFrame {

  public function __construct($data, HTMLGenerator $html) {
    parent::__construct($data, $html);

    $this->cacheT8('manix/util/users/common');
  }

  public function frame() {
    if ($this->data === true) {
      $this->success();
    } elseif ($this->data === false) {
      $this->fail();
    } else {
      $this->form();
    }
  }

  protected function form() {
    $form = new Form();
    $form->setAttribute('id', 'autosubmit');
    $form->setMethod('PUT');
    $form->setAction(route(Verify::class));
    $form->add('email', 'email', $this->data['address'] ?? '');
    $form->add('code', 'text', $this->data['code'] ?? '');

    $view = new DefaultFormView($form, $this->html);
    ?>

    <?= $view ?>

    <script>document.getElementById("autosubmit").submit();</script>

    <?php
  }

  protected function success() {
    ?>
    <div class="alert alert-success text-center">
      <span><?= $this->t8('verifiedSuccess') ?></span>
      <hr/>
      <a class="btn btn-success" href="<?= route(Login::class) ?>">
        <?= $this->t8('continue') ?>
      </a>
    </div>
    <?php
  }

  protected function fail() {
    ?>
    <div class="alert alert-danger">
      <?= $this->t8('verifiedFail') ?>
    </div>
    <?php
  }

  public function heading() {
    ?>
    <h2><?= $this->t8('verifyAddr') ?></h2>
    <?php
  }

}
