<?php

namespace Manix\Brat\Utility\Users\Views\Settings;

use Manix\Brat\Helpers\HTMLGenerator;
use Manix\Brat\Utility\Users\Controllers\Settings\Emails;
use Manix\Brat\Utility\Users\Controllers\Settings\Name;
use Manix\Brat\Utility\Users\Controllers\Settings\Password;
use Manix\Brat\Utility\Users\Controllers\Settings\Photo;
use Manix\Brat\Utility\Users\Models\Auth;
use Project\Views\Layouts\DefaultLayout;
use function html;
use function route;
use function url;

abstract class DefaultSettingsLayout extends DefaultLayout {

  public $title = 'Settings';

  public function __construct($data, HTMLGenerator $html) {
    parent::__construct($data, $html);

    $this->cacheT8('manix/util/users/settings');
  }

  /**
   * Define an array that represents the settings menu. Format:
   * [
   *  String $groupLabel => [
   *    String $controller => String $settingLabel,
   *    ...
   *  ],
   *  ...
   * ]
   */
  protected function getMenuItems() {
    return [
        $this->t8('profileInfo') => [
            Name::class => $this->t8('name'),
            Photo::class => $this->t8('photo')
        ],
        $this->t8('loginInfo') => [
            Emails::class => $this->t8('emails'),
            Password::class => $this->t8('password')
        ]
    ];
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
            <?php foreach ($this->getMenuItems() as $groupLabel => $settings): ?>
              <div class="card-header">
                <?= $groupLabel ?>
              </div>
              <div class="list-group">
                <?php
                foreach ($settings as $class => $settingLabel) {
                  $this->menuItem($class, $settingLabel);
                }
                ?>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="col-sm-9">
          <?php
          if (isset($this->data['success'])) {
            echo $this->getSuccessCard($this->data['success']);
          } elseif (isset($this->data['error'])) {
            echo $this->getErrorCard($this->data['error']);
          } else {
            ?>
            <div class="card">
              <?= $this->card() ?>
            </div>
          <?php } ?>
        </div>
      </div>
    </div>
    <?php
  }

  protected function getSuccessCard($success) {
    ?>
    <div class="card card-inverse card-success">
      <div class="card-block">
        <p class="mb-3">
          <?= $success === true ? $this->t8('changesSaved') : $success ?>
        </p>

        <a href="<?= url() ?>" class="btn btn-success pull-right">
          <?= $this->t8('manix/util/users/common', 'continue') ?>
        </a>
      </div>
    </div>
    <?php
  }

  protected function getErrorCard($error) {
    ?>
    <div class="card card-inverse card-danger">
      <div class="card-block">
        <p class="mb-3"><?= $error ?></p>

        <a href="<?= url() ?>" class="btn btn-danger pull-right">
          <?= $this->t8('manix/util/users/common', 'continue') ?>
        </a>
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
