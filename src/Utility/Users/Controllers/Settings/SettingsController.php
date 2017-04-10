<?php

namespace Manix\Brat\Utility\Users\Controllers\Settings;

use Manix\Brat\Helpers\FormController;
use Manix\Brat\Utility\Users\Models\Auth;
use Manix\Brat\Utility\Users\Views\Settings\DefaultSettingsView;

abstract class SettingsController extends FormController {

  public $page = DefaultSettingsView::class;

  public function get() {
    return ['form' => $this->getForm()];
  }

  public function __construct() {
    Auth::required();

    $this->cacheT8('manix/util/users/settings');

    $this->data['ctrl'] = $this;
  }

  protected function requireCurrentPassword() {
    $this->getForm()->add('currentPassword', 'password');
    $this->getRules()->add('currentPassword')->required()->callback(function($password) {
      if (!Auth::user()->validatePassword($password)) {
        return $this->t8('manix/util/users/common', 'wrongPass');
      }
    });
  }

  protected function addSaveButton() {
    $this->getForm()->add('', 'submit', $this->t8('save'));
  }

}
