<?php

namespace Manix\Brat\Utility\Users\Controllers\Social;

use Exception;

class Linkedin extends ProviderController {

  public function provider() {
    return \League\OAuth2\Client\Provider\LinkedIn::class;
  }

  public function redirect($provider) {
    return $provider->getAuthorizationUrl([
    // 'scope' => ['email'],
    ]);
  }

  public function fetch($provider, $token) {

    // We got an access token, let's now get the user's details
    $user = $provider->getResourceOwner($token);

    return [
        'name' => $user->getFirstName() . ' ' . $user->getLastName(),
        'email' => $user->getEmail(),
        'img' => $user->getImageurl()
    ];
  }

}
