<?php

namespace Manix\Brat\Utility\Users\Views;

use Project\Views\Layouts\DefaultLayout;

abstract class GuestFrame extends DefaultLayout {

  public function body() {
    $this->cacheT8('manix/util/users/common');
    ?>
    <div class="jumbotron text-center">
      <?= $this->heading() ?>
    </div>


    <div class="container" style="max-width: 480px">
      <?= $this->frame() ?>
    </div>
    <?php
  }

  abstract public function heading();

  abstract public function frame();
}
