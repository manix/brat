<?php

namespace Manix\Brat\Utility\Users\Views\Settings;

class IndexView extends DefaultSettingsLayout {

  public function card() {
    ?>

    <div class="card-block">

      <?= $this->t8('index') ?>

    </div>

    <?php
  }

}