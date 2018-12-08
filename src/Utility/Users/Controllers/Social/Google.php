<?php

namespace Manix\Brat\Utility\Users\Controllers\Social;

use Exception;

class Google extends ProviderController {

  public function provider() {
    return \League\OAuth2\Client\Provider\Google::class;
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
        'name' => $user->getName(),
        'email' => $user->getEmail(),
        'img' => $user->getAvatar()
    ];
  }

}
