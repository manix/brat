<?php

namespace Manix\Brat\Utility\Captcha;

use Manix\Brat\Utility\HTTP\HTTPController;

class CaptchaController extends HTTPController {

  public $page = CaptchaView::class;

  /**
   * Must return the required length of a code.
   * @return int
   */
  protected function codeLength(): int {
    return 4;
  }

  public function get() {
    $manager = new CaptchaManager();
    
    return $manager->generateImage($this->codeLength());
  }

}
