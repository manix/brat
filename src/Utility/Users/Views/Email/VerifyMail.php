<?php

namespace Manix\Brat\Utility\Users\Views\Email;

use Manix\Brat\Components\Views\HTML\HTMLElement;
use Manix\Brat\Helpers\HTMLGenerator;
use Manix\Brat\Utility\Users\Controllers\Verify as VC;
use Manix\Brat\Utility\Users\Models\UserEmail;
use const SITE_URL;
use function config;
use function html;
use function route;

class VerifyMail extends HTMLElement {

  public function __construct(UserEmail $data, HTMLGenerator $html) {
    parent::__construct($data, $html);
    $this->cacheT8('manix/util/users/emails');
  }

  public function html() {
    $url = route(VC::class, [
        'address' => $this->data->email,
        'code' => $this->data->getCode()
    ]);
    ?>
    <h1>
      <a href="<?= SITE_URL ?>">
        <?= html(config('project')['name']) ?>
      </a>
    </h1>
    <p><?= $this->t8('greeting') ?></p>
    <p><?= $this->t8('thanksForRegistering') ?></p>
    <p><?= $this->t8('clickToVerify') ?></p>
    <p>
      <a href="<?= $url ?>">
        <?= $url ?>
      </a>
    </p>
    <p><?= $this->t8('bestWishes') ?></p>
    <?php
  }

}
