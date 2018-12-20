<?php

namespace Manix\Brat\Utility\Users\Controllers\Settings;

use Exception;
use Manix\Brat\Components\Criteria;
use Manix\Brat\Components\Forms\Form;
use Manix\Brat\Components\Validation\Ruleset;
use Project\Traits\Users\UserGatewayFactory;
use Manix\Brat\Utility\Users\Models\Auth;

class Password extends SettingsController {

  use UserGatewayFactory;

  public function before($method) {
    $this->requireCurrentPassword();
    $this->addSaveButton();

    return parent::before($method);
  }

  public function get() {

    return [
        'form' => $this->getForm()
    ];
  }

  public function put() {
    return $this->validate($_POST, function($data) {
      $gate = $this->getUserGateway();

      $user = Auth::user();

      $user->setPassword($data['new']);

      if (!$gate->persist($user)) {
        throw new Exception('Persistence error.', 500);
      }

      Auth::register($user);

      $tokenGate = $this->getTokenGateway();
      $criteria = new Criteria;
      $criteria->equals('user_id', $user->id);
      $tokenGate->wipeBy($criteria);

      return ['success' => true];
    });
  }

  protected function constructForm(Form $form): Form {
    $form->setMethod('PUT');
    $form->add('new', 'password');
    $form->add('new_rpt', 'password');

    return $form;
  }

  protected function constructRules(Ruleset $rules): Ruleset {
    $rules->add('new')->required()->length(8, 255);
    $rules->add('new_rpt')->required()->equals($_POST['new'] ?? null, $this->t8('passNoMatch'));

    return $rules;
  }

}
