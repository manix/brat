<?php

namespace Manix\Brat\Utility\Users\Views;

use Project\Views\Users\GuestFrame;

class RegisterSuccessView extends GuestFrame {

  public function frame() {
    ?>
    <div class="alert alert-success">
      <?= $this->t8('registerSuccess') ?>
    </div>
    <?php
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
