<?php

namespace Manix\Brat\Utility\Users\Views\Settings;

use Manix\Brat\Helpers\HTMLGenerator;
use Manix\Brat\Utility\Users\Controllers\Settings\Emails;
use Manix\Brat\Utility\Users\Controllers\Settings\Name;
use Manix\Brat\Utility\Users\Controllers\Settings\Password;
use Manix\Brat\Utility\Users\Models\Auth;
use Project\Views\Layouts\DefaultLayout;
use function html;
use function route;

abstract class DefaultSettingsLayout extends DefaultLayout {

  public $title = 'Settings';

  public function __construct($data, HTMLGenerator $html) {
    parent::__construct($data, $html);

    $this->cacheT8('manix/util/users/settings');
  }

  public function body() {
    ?>

    <div class="jumbotron text-center mb-3">
      <h2><?= html(Auth::name()) ?></h2>
      <p><?= $this->t8('settings') ?></p>
    </div>

    <div class="container-fluid">
      <div class="row">
        <div class="col-sm-3">
          <div class="card">
            <div class="card-header">
              <?= $this->t8('profileInfo') ?>
            </div>
            <div class="list-group">
              <?= $this->menuItem(Name::class, $this->t8('name')) ?>
            </div>
            <div class="card-header">
              <?= $this->t8('loginInfo') ?>
            </div>
            <div class="list-group">
              <?= $this->menuItem(Emails::class, $this->t8('emails')) ?>
              <?= $this->menuItem(Password::class, $this->t8('password')) ?>
            </div>
          </div>
        </div>
        <div class="col-sm-9">
          <?php if (isset($this->data['success'])): ?>
            <div class="card card-inverse card-success">
              <div class="card-block">
                <p class="mb-3">
                  <?= $this->data['success'] === true ? $this->t8('changesSaved') : $this->data['success'] ?>
                </p>

                <a href="<?= url() ?>" class="btn btn-success pull-right">
                  <?= $this->t8('manix/util/users/common', 'continue') ?>
                </a>
              </div>
            </div>
          <?php elseif (isset($this->data['error'])): ?>
            <div class="card card-inverse card-danger">
              <div class="card-block">
                <p class="mb-3"><?= $this->data['error'] ?></p>

                <a href="<?= url() ?>" class="btn btn-danger pull-right">
                  <?= $this->t8('manix/util/users/common', 'continue') ?>
                </a>
              </div>
            </div>
          <?php else: ?>
            <div class="card">
              <?= $this->card() ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php
  }

  protected function menuItem($controller, $label) {
    $active = ($this->data['ctrl'] ?? null) instanceof $controller;
    ?>
    <a href="<?= route($controller) ?>" class="list-group-item <?= $active ? 'active' : null ?>">
      <?= $label ?>
    </a>
    <?php
  }

  abstract public function card();
}
