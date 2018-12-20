<?php

namespace Manix\Brat\Utility\Users\Views\Settings;

use Project\Views\Users\DefaultSettingsLayout;

class IndexView extends DefaultSettingsLayout {

  public function card() {
    ?>

    <div class="card-body">

      <?= $this->t8('index') ?>

    </div>

    <?php
  }

}
