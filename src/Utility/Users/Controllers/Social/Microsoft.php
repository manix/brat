<?php

namespace Manix\Brat\Utility\Users\Controllers\Social;

class Microsoft extends ProviderController {

  public static function icon() {
    return 'windows';
  }

  public function provider() {
    return \Stevenmaguire\OAuth2\Client\Provider\Microsoft::class;
  }

  public function redirect($provider) {
    $provider->defaultScopes[] = 'wl.photos';

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
        'img' => null // Microsoft seems to not provide image information
    ];
  }

}
