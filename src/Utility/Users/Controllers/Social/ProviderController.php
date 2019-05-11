<?php

namespace Manix\Brat\Utility\Users\Controllers\Social;

use Manix\Brat\Utility\HTTP\HTTPController;
use Manix\Brat\Helpers\Redirect;
use Exception;
use ReflectionClass;

abstract class ProviderController extends HTTPController {

  public static function icon() {
    return 'fa fa-' . (new ReflectionClass(static::class))->getShortName();
  }

  abstract public function provider();

  abstract public function redirect($provider);

  abstract public function fetch($provider, $token);

  public final function get() {
    $config = config('social');
    $providerConfig = $config['providers'][static::class];
    $providerConfig['redirectUri'] = route(static::class);

    $class = $this->provider();
    $provider = new $class($providerConfig);

    if (!isset($_GET['code'])) {

      // If we don't have an authorization code then get one
      $authUrl = $this->redirect($provider);
      $_SESSION['oauth2state'] = $provider->getState();

      return new Redirect($authUrl);
      // Check given state against previously stored one to mitigate CSRF attack
    } elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {

      unset($_SESSION['oauth2state']);
      throw new Exception('Invalid state.', 503);
    }

    // Try to get an access token (using the authorization code grant)
    $token = $provider->getAccessToken('authorization_code', [
        'code' => $_GET['code']
    ]);

    // Optional: Now you have a token you can look up a users profile data
    try {

      $registrarData = $this->fetch($provider, $token);
    } catch (\Exception $e) {

      // Failed to get user details
      throw $e;
    }

    $registrar = new $config['registrar'];

    $registrar->register($registrarData);
  }

}
