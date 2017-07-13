<?php

namespace Manix\Brat\Utility\CRUD;

use Manix\Brat\Components\Persistence\Gateway;

trait CRUDFoundation {

  protected $crud_gateway;

  public final function getGateway(): Gateway {
    if ($this->crud_gateway === null) {
      $this->crud_gateway = $this->constructGateway();
    }
    
    return $this->crud_gateway;
  }

  abstract protected function constructGateway(): Gateway;
}
