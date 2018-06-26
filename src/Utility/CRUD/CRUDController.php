<?php

namespace Manix\Brat\Utility\CRUD;

use Manix\Brat\Helpers\FormController;

/**
 * @deprecated Use CRUDEndpoint
 */
abstract class CRUDController extends FormController {

  use CRUDEndpoint;
  
}
