<?php

namespace Manix\Brat\Components\Errors;

use Manix\Brat\Components\Controller;
use Throwable;

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
        'throwable' => $this->throwable,
        'code' => $this->throwable->getCode(),
        'message' => $this->throwable->getMessage()
    ];
  }
}
