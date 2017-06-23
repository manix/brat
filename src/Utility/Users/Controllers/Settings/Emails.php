<?php

namespace Manix\Brat\Utility\Users\Controllers\Settings;

use Exception;
use Manix\Brat\Components\Criteria;
use Manix\Brat\Components\Forms\Form;
use Manix\Brat\Components\Validation\Ruleset;
use Manix\Brat\Utility\Users\Controllers\GatewayFactory;
use Manix\Brat\Utility\Users\Controllers\Mailer;
use Manix\Brat\Utility\Users\Models\Auth;
use Manix\Brat\Utility\Users\Models\UserEmail;
use Manix\Brat\Utility\Users\Views\Settings\EmailsView;
use Project\Utility\Users\Controllers\SettingsController;

class Emails extends SettingsController {

  use GatewayFactory,
      Mailer;

  public $page = EmailsView::class;

  public function __construct() {
    parent::__construct();

    $this->requireCurrentPassword();
  }

  public function get() {
    $gate = $this->getEmailGateway();
    $criteria = new Criteria;
    $criteria->equals('user_id', Auth::id());
    $rm = new Form();
    $rm->setMethod('DELETE');

    return [
        'form' => $this->getForm(),
        'addresses' => $gate->findBy($criteria),
        'delete' => $rm
    ];
  }

  public function post() {
    return $this->validate($_POST, function($data, $validator) {
      $egate = $this->getEmailGateway();

      $email = $egate->find($data['email'])->first();

      if ($email) {
        $validator->setError('email', $this->t8('manix/util/users/common', 'emailTaken'));
      } else {
        $email = new UserEmail([
            'user_id' => Auth::id(),
            'email' => $data['email']
        ]);
        $email->invalidate();

        if ($egate->persist($email)) {
          $this->sendActivationMail($email);

          return ['success' => $this->t8('emailAdded')];
        }
      }

      return $this->defaultFailAction($data, $validator);
    });
  }

  public function delete() {
    $criteria = new Criteria();
    $criteria->equals('user_id', Auth::id());

    $gate = $this->getEmailGateway();

    $emails = $gate->findBy($criteria);

    if (!$emails->count()) {

      throw new Exception('Unexpected', 500);
    } else {

      $target = $emails->find('email', $_POST['email'] ?? null);

      if (!$target) {

        throw new Exception('Bad request', 400);
      } else {

        if ($target->isVerified()) {
          $verified = 0;

          foreach ($emails as $email) {
            if ($email->isVerified()) {
              $verified++;
            }
          }

          if ($verified < 2) {
            return ['error' => $this->t8('minimumVerifiedMails')];
          }
        }
      }
    }

    $gate->wipe($target->email);

    return $this->get();
  }

  protected function constructForm(Form $form): Form {
    $form->add('email', 'email');

    return $form;
  }

  protected function constructRules(Ruleset $rules): Ruleset {
    $rules->add('email')->required()->email();

    return $rules;
  }

}
