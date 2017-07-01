<?php

namespace Manix\Brat\Utility\Users\Views;

use Project\Views\Users\GuestFrame;
use const SITE_URL;

class LoginSuccessView extends GuestFrame {

  public function frame() {
    ?>
    <div class="alert alert-success text-center">
      <?= $this->t8('loginSuccess') ?>
      <hr/>
      <a href="<?= $this->data['backto'] ?? SITE_URL ?>" class="btn btn-success">
        <?= $this->t8('continue') ?>
      </a>
    </div>
    <?php
  }

  public function heading() {
    ?>
    <h2><?= $this->t8('login') ?></h2>
    <?php
  }

}
