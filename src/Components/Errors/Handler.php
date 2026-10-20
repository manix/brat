<?php

namespace Manix\Brat\Components\Errors;

use Manix\Brat\Components\Controller;
use stdClass;
use Throwable;
use const DEBUG_MODE;

/**
 * The default error handler
 */
class Handler extends Controller {
  
  public $page = View::class;
  protected $throwable;

  public function __construct(Throwable $throwable) {
    $this->throwable = $throwable;
  }
  
  public function before($method) {
    parent::before($method);
    
    return 'handle';
  }

  public function handle() {
    return [
        // the object only in debug mode: text/plain prints it whole, trace included. Clients only check that it is there
        'throwable' => DEBUG_MODE ? $this->throwable : new stdClass(),
        'code' => $this->throwable->getCode(),
        'message' => $this->throwable->getMessage()
    ];
  }
}
