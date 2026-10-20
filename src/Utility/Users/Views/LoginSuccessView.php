<?php

namespace Manix\Brat\Utility\Users\Views;

use Manix\Brat\Helpers\URL;
use Project\Views\Users\GuestFrame;
use const SITE_URL;
use function html;

class LoginSuccessView extends GuestFrame {

  public function frame() {
    ?>
    <div class="alert alert-success text-center">
      <?= $this->t8('loginSuccess') ?>
      <hr/>
      <?php $backto = $this->data['backto'] ?? '' ?>
      <a href="<?= html((new URL($backto))->isLocal() ? $backto : SITE_URL) ?>" class="btn btn-success">
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
