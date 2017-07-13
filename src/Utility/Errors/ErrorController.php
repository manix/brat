<?php

namespace Manix\Brat\Utility\Errors;

use Manix\Brat\Components\Controller;
use Throwable;

class ErrorController extends Controller {

  public $page = DebugErrorView::class;
  protected $throwable;

  public function __construct(Throwable $throwable) {
    $this->throwable = $throwable;
  }

  public function display() {
    $code = (int)$this->throwable->getCode();
    
    http_response_code($code > 99 && $code < 600 ? $code : 500);
    
    return $this->throwable;
  }

}
