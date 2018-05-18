<?php

namespace Manix\Brat\Utility\Users\Controllers;

use Manix\Brat\Components\Persistence\Gateway;
use Manix\Brat\Utility\Users\Models\UserEmailGateway;
use Manix\Brat\Utility\Users\Models\UserGateway;
use Manix\Brat\Utility\Users\Models\UserLoginGateway;
use Manix\Brat\Utility\Users\Models\UserTokenGateway;

trait UserGatewayFactory {

  /**
   * @return UserEmailGateway
   */
  protected function getEmailGateway($fields = null) {
    return $this->constructUserGateway($this->getEmailGatewayClass(), $fields);
  }
  
  protected function getEmailGatewayClass() {
    return UserEmailGateway::class;
  }

  /**
   * @return UserGateway
   */
  protected function getUserGateway($fields = null) {
    return $this->constructUserGateway($this->getUserGatewayClass(), $fields);
  }
  
  protected function getUserGatewayClass() {
    return UserGateway::class;
  }

  /**
   * @return UserTokenGateway
   */
  protected function getTokenGateway($fields = null) {
    return $this->constructUserGateway($this->getTokenGatewayClass(), $fields);
  }
  
  protected function getTokenGatewayClass() {
    return UserTokenGateway::class;
  }

  /**
   * @return UserLoginGateway
   */
  protected function getLoginGateway($fields = null) {
    return $this->constructUserGateway($this->getLoginGatewayClass(), $fields);
  }
  
  protected function getLoginGatewayClass() {
    return UserLoginGateway::class;
  }

  protected function setGatewayFields(Gateway $gate, $fields) {
    if ($fields) {
      return $gate->setFields($fields);
    }
    
    return $gate;
  }
  
  protected function constructUserGateway($class, $fields = null) {
    $gate = new $class;
    
    if ($fields) {
      $gate->setFields($fields);
    }
    
    return $gate;
  }
}
