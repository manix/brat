<?php

namespace Manix\Brat\Helpers;

class Redirect {

  /**
   * Respond with a redirect to the HTTP request.
   * @param string $url The target location.
   * @param bool $permanent Whether to send 301 or 302 HTTP response code.
   * @param bool $exit Whether to exit the script immediately after setting the Location header.
   */
  public function __construct($url, $permanent = false, $exit = true) {
    http_response_code($permanent ? 301 : 302);
    header('Location: ' . $url);
    
    if ($exit) {
      exit;
    }
  }
  
}
