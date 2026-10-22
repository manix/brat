<?php

namespace Manix\Brat\Utility\Users\Controllers\Settings;

use Manix\Brat\Components\Forms\Form;
use Manix\Brat\Components\Validation\Ruleset;
use Manix\Brat\Helpers\FormController;
use Manix\Brat\Utility\Users\Models\Auth;
use Manix\Brat\Utility\Users\Views\Settings\DefaultSettingsView;

abstract class SettingsController extends FormController {

  public $page = DefaultSettingsView::class;

  protected $currentPassword = false;

  public function get() {
    return ['form' => $this->getForm()];
  }

  public function middleware() {
    return ['auth'];
  }

  public function before($method) {
    $this->cacheT8('manix/util/users/settings');

    return parent::before($method);
  }

  public function after($data) {
    $data['ctrl'] = $this;

    return parent::after($data);
  }

  // only records the requirement: before() runs ahead of the lang middleware, so the form
  // and rules (and their translations) are built on first use, after the language is set
  protected function requireCurrentPassword() {
    $this->currentPassword = true;
  }

  protected function getForm(): Form {
    if ($this->form === null && $this->currentPassword) {
      parent::getForm()->add('currentPassword', 'password');
    }

    return parent::getForm();
  }

  protected function getRules(): Ruleset {
    if ($this->rules === null && $this->currentPassword) {
      parent::getRules()->add('currentPassword')->required()->callback(function($password) {
        if (!Auth::user()->validatePassword($password)) {
          return $this->t8('manix/util/users/common', 'wrongPass');
        }
      });
    }

    return parent::getRules();
  }

  protected function addSaveButton() {
    $this->getForm()->add('save', 'submit', $this->t8('manix/util/users/settings', 'save'));
  }

}
