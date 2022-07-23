<?php

namespace Manix\Brat\Helpers;

class Redirect {

  /**
   * Respond with a redirect to the HTTP request.
   * @param string $url The target location.
   * @param mixed $code The response code. Can be bool ($permanent) to support legacy code.
   * @param bool $exit Whether to exit the script immediately after setting the Location header.
   */
  public function __construct($url, $code = false, $exit = true) {
    http_response_code($code < 2 ? (302 - $code) : $code);
    header('Location: ' . $url);
    
    if ($exit) {
      exit;
    }
  }
  
}
