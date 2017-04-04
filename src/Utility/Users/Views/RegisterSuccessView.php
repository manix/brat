<?php

namespace Manix\Brat\Utility\Users\Views;

use Manix\Brat\Utility\BootstrapLayout;

class RegisterSuccessView extends BootstrapLayout {

  public function body() {
    ?>

    <div class="jumbotron text-center">
      <h2><?= $this->t8('manix/util/users/common', 'register') ?></h2>
    </div>

    <div class="container" style="max-width: 480px">
      <div class="alert alert-success">
        <?= $this->t8('manix/util/users/common', 'registerSuccess') ?>
        <div class="text-center">
          <a href="login" class="btn btn-success">
            <?= $this->t8('manix/util/users/common', 'login') ?>
          </a>
        </div>
      </div>
    </div>

    <?php
  }

}
