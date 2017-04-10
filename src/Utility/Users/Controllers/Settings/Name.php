<?php

namespace Manix\Brat\Utility\Users\Controllers\Settings;

use Exception;
use Manix\Brat\Components\Forms\Form;
use Manix\Brat\Components\Validation\Ruleset;
use Manix\Brat\Utility\Users\Controllers\GatewayFactory;
use Manix\Brat\Utility\Users\Models\Auth;

class Name extends SettingsController {

  use GatewayFactory;

  public function __construct() {
    parent::__construct();

    $this->addSaveButton();
  }

  public function put() {

    return $this->validate($_POST, function($data) {
      $user = Auth::user();

      $user->name = $data['name'];

      if ($this->getUserGateway()->persist($user)) {
        Auth::register($user);

        return ['success' => true];
      }

      throw new Exception('Unexpected', 500);
    });
  }

  protected function constructForm(Form $form): Form {
    $form->setMethod('PUT');
    $form->add('name', 'text', Auth::name());

    return $form;
  }

  protected function constructRules(Ruleset $rules): Ruleset {
    $rules->add('name')->required()->alphabeticX('\' -');

    return $rules;
  }

}
