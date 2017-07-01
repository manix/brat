<?php

namespace Manix\Brat\Utility\Users\Controllers;

use Manix\Brat\Utility\Users\Models\UserEmailGateway;
use Manix\Brat\Utility\Users\Models\UserGateway;
use Manix\Brat\Utility\Users\Models\UserLoginGateway;
use Manix\Brat\Utility\Users\Models\UserTokenGateway;

trait UserGatewayFactory {

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

  /**
   * @return UserTokenGateway
   */
  protected function getTokenGateway() {
    return new UserTokenGateway;
  }

  /**
   * @return UserLoginGateway
   */
  protected function getLoginGateway() {
    return new UserLoginGateway();
  }

}
