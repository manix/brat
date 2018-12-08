<?php

namespace Manix\Brat\Utility\Users\Controllers\Social;

use Exception;

class Reddit extends ProviderController {

  public function provider() {
    return \Rudolf\OAuth2\Client\Provider\Reddit::class;
  }

  public function redirect($provider) {
    return $provider->getAuthorizationUrl([
      // 'scope' => ['email'],
    ]);
  }

  public function fetch($provider, $token) {

    // We got an access token, let's now get the user's details
    $user = $provider->getResourceOwner($token);

    // Use these details to create a new profile
    // printf('Hello %s!', $user->getFirstName());
    
    echo '<pre>';
    var_dump($user);
    # object(League\OAuth2\Client\Provider\FacebookUser)#10 (1) { ...
    echo '</pre>';
  }
}