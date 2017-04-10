<?php

namespace Manix\Brat\Utility\Users\Views\Email;

use Manix\Brat\Components\Views\HTML\HTMLElement;
use Manix\Brat\Utility\Users\Controllers\Reset;
use const SITE_URL;
use function config;
use function html;
use function route;

class ForgotMail extends HTMLElement {

  public function html() {
    $this->cacheT8('manix/util/users/emails');
    
    $url = route(Reset::class, [
        'id' => $this->data['id'],
        'code' => $this->data['code'] ?? null
    ]);
    ?>
    <h1>
      <a href="<?= SITE_URL ?>">
        <?= html(config('project')['name']) ?>
      </a>
    </h1>
    <p><?= $this->t8('greeting') ?></p>
    <p><?= $this->t8('clickToResetPass') ?></p>
    <p>
      <a href="<?= $url ?>">
        <?= $url ?>
      </a>
    </p>
    <p><?= $this->t8('bestWishes') ?></p>
    <?php
  }

}
