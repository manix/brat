<?php

namespace Manix\Brat\Utility\Users\Controllers;

use Manix\Brat\Utility\Users\Models\UserEmailGateway;
use Manix\Brat\Utility\Users\Models\UserGateway;

trait GatewayFactory {

  /**
   * @return UserEmailGateway
   */
  protected function getEmailGateway() {
    return new UserEmailGateway;
  }

  /**
   * @return UserGateway
   */
  protected function getUserGateway() {
    return new UserGateway;
  }

}
