<?php

namespace Manix\Brat\Utility\Users\Controllers\Settings;

use Exception;
use Manix\Brat\Components\Criteria;
use Manix\Brat\Components\Forms\Form;
use Manix\Brat\Components\Model;
use Manix\Brat\Components\Validation\Ruleset;
use Manix\Brat\Utility\Users\Models\Auth;
use Manix\Brat\Utility\Users\Models\MFATotp;
use Manix\Brat\Utility\Users\Views\Settings\MFAView;
use Project\Traits\Users\UserGatewayFactory;
use const SITE_DOMAIN;

/**
 * Turns MFA with an authenticator app on and off. GET shows a new secret (kept in the
 * session until it is confirmed) with its otpauth:// address for a QR code, POST turns it
 * on with the current password and a code from the app, DELETE turns it off with the
 * current password.
 */
class MFA extends SettingsController {

  use UserGatewayFactory;

  public $page = MFAView::class;

  public function before($method) {
    $this->requireCurrentPassword();

    return parent::before($method);
  }

  /**
   * The name the authenticator app shows the account under.
   */
  protected function issuer() {
    return SITE_DOMAIN;
  }

  public function get() {
    $form = $this->getForm();
    $totp = $this->getMFAGateway(['user_id', 'method'])->find(Auth::id())->find('method', 'totp');

    if ($totp) {
      $form->setMethod('DELETE');
      $form->remove('code');
      $form->add('remove', 'submit', $this->t8('mfaRemove'));

      return [
          'form' => $form,
          'totp' => $totp
      ];
    }

    $secret = $_SESSION[MANIX]['mfasetup'] ??= MFATotp::secret();
    $this->addSaveButton();

    return [
        'form' => $form,
        'secret' => $secret,
        'uri' => MFATotp::uri($secret, Auth::name(), $this->issuer())
    ];
  }

  public function post() {
    return $this->validate($_POST, function ($data, $v) {
      $secret = $_SESSION[MANIX]['mfasetup'] ?? null;
      $record = new Model([
          'user_id' => Auth::id(),
          'method' => 'totp',
          'data' => $secret,
          'used' => 0
      ]);

      if (!$secret || !(new MFATotp())->verify($record, (string)$data['code'])) {
        $v->setError('code', $this->t8('manix/util/users/common', 'mfaWrongCode'));

        return $this->defaultFailAction($data, $v);
      }

      if (!$this->getMFAGateway()->persist($record)) {
        throw new Exception('Persistence error.', 500);
      }

      unset($_SESSION[MANIX]['mfasetup']);

      // remember-me tokens log in without a code: end the ones issued before, like a password change
      $criteria = new Criteria();
      $criteria->equals('user_id', Auth::id());
      $this->getTokenGateway()->wipeBy($criteria);

      return ['success' => true];
    });
  }

  public function delete() {
    $this->getRules()->remove('code');

    return $this->validate($_POST, function () {
      $this->getMFAGateway()->wipe(Auth::id(), 'totp');

      return ['success' => true];
    });
  }

  protected function constructForm(Form $form): Form {
    $form->add('code', 'text')
        ->setAttribute('autocomplete', 'one-time-code')
        ->setAttribute('inputmode', 'numeric');

    return $form;
  }

  protected function constructRules(Ruleset $rules): Ruleset {
    $rules->add('code')->required();

    return $rules;
  }

}
