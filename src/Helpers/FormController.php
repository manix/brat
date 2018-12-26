<?php

namespace Manix\Brat\Helpers;

use Manix\Brat\Utility\HTTP\HTTPController;

/**
 * @deprecated use FormEndpoint trait instead
 */
abstract class FormController extends HTTPController {

  use FormEndpoint;
}
