<?php

namespace Manix\Brat\Utility\Users\Controllers;

use Exception;
use Manix\Brat\Components\Forms\Form;
use Manix\Brat\Components\Validation\Ruleset;
use Manix\Brat\Helpers\FormController;
use Manix\Brat\Utility\Users\Models\MFAMethods;
use Manix\Brat\Utility\Users\Views\MFAView;
use Project\Traits\Users\UserGatewayFactory;
use function cache;
use function route;

/**
 * The login's second step: Login::completeLogin keeps the user in the session after the
 * right password, and this asks for the code of one of their MFA methods (`method`, the
 * first one by default). The right code finishes the login through Login::completeLogin.
 */
class MFA extends FormController {

  use UserGatewayFactory;

  // seconds the right password stays valid while the code is asked for
  const PENDING_TTL = 300;

  public $page = MFAView::class;

  /**
   * How many wrong codes a user gets before being blocked.
   */
  protected function allowedAttempts(): int {
    return 5;
  }

  /**
   * How long a user is blocked after too many wrong codes, in seconds.
   */
  protected function blockFor(): int {
    return 600;
  }

  /**
   * The login controller to finish the login with, the project's subclass if it has one.
   * @return Login
   */
  protected function getLoginController() {
    return new Login();
  }

  /**
   * The login waiting for the code, or null when there is none or it expired.
   */
  protected function pending() {
    $pending = $_SESSION[MANIX]['mfa'] ?? null;

    return $pending && $pending['until'] > time() ? $pending : null;
  }

  public function get() {
    $pending = $this->pending();

    if (!$pending) {
      throw new Exception($this->t8('manix/util/users/common', 'mfaExpired'), 403);
    }

    return [
        'mfa' => $pending['methods'],
        'form' => $this->getForm()
    ];
  }

  public function post() {
    $pending = $this->pending();

    if (!$pending) {
      throw new Exception($this->t8('manix/util/users/common', 'mfaExpired'), 403);
    }

    return $this->validate($_POST, function ($data, $v) use ($pending) {
      $user = $pending['user'];
      // per user, not per address: whoever is here already has the password
      $key = 'users/mfablock/' . $user->id;
      // counted before the code is checked, atomically: codes sent at the same time each count
      $attempts = cache()->increment($key, $this->blockFor());
      $name = $data['method'] ?? $pending['methods'][0];

      if ($attempts > $this->allowedAttempts()) {
        $v->setError('code', $this->t8('manix/util/users/common', 'loginBlocked'));
      } else if (!in_array($name, $pending['methods'], true)) {
        throw new Exception('Bad request', 400);
      } else {
        $gate = $this->getMFAGateway();
        $record = $gate->find($user->id, $name)->first();
        $method = MFAMethods::get($name);
        $used = $record ? $record->used : null;

        if ($record && $method && $method->verify($record, (string)$data['code']) && $gate->markUsed($record, $used)) {
          cache()->wipe($key);
          unset($_SESSION[MANIX]['mfa']);

          $login = $this->getLoginController();
          $response = $login->completeLogin($user, $pending['remember'], true);
          $this->page = $login->page;

          return $response;
        }

        $v->setError('code', $this->t8('manix/util/users/common', 'mfaWrongCode'));
      }

      return $this->defaultFailAction($data, $v);
    });
  }

  protected function constructForm(Form $form): Form {
    $backto = $this->pending()['backto'] ?? null;

    $form->setAction(route(static::class, $backto ? ['b' => $backto] : []));
    $form->add('code', 'text')
        ->setAttribute('autocomplete', 'one-time-code')
        ->setAttribute('inputmode', 'numeric')
        ->setAttribute('autofocus', 'autofocus');
    $form->add('verify', 'submit', $this->t8('manix/util/users/common', 'mfaVerify'));

    return $form;
  }

  protected function constructRules(Ruleset $rules): Ruleset {
    $rules->add('code')->required();

    return $rules;
  }

}
