<?php

namespace Manix\Brat\Utility\Users\Views;

use Manix\Brat\Helpers\FormViews\DefaultFormView;
use Project\Views\Users\GuestFrame;

class MFAView extends GuestFrame {

  public function frame() {
    ?>
    <p><?= $this->t8('mfaHelp') ?></p>
    <?php
    echo new DefaultFormView($this->data['form'], $this->html, [
        'code' => $this->t8('mfaCode')
    ]);
  }

  public function heading() {
    ?>
    <h2><?= $this->t8('mfaTitle') ?></h2>
    <?php
  }

}
