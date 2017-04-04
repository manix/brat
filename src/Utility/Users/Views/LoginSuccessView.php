<?php

namespace Manix\Brat\Utility\Users\Views;

use Manix\Brat\Utility\BootstrapLayout;

class LoginSuccessView extends BootstrapLayout {

  public function body() {
    ?>

    <div class="jumbotron text-center">
      <h2><?= $this->t8('manix/util/users/common', 'login') ?></h2>
    </div>

    <div class="container" style="max-width: 480px">
      <div class="alert alert-success text-center">
        <?= $this->t8('manix/util/users/common', 'loginSuccess') ?>
        <hr/>
        <a href="<?= $this->data['backto'] ?? SITE_URL ?>" class="btn btn-success">
          <?= $this->t8('manix/util/users/common', 'continue') ?>
        </a>
      </div>
    </div>

    <?php
  }

}
