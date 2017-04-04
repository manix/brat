<?php

namespace Manix\Brat\Utility\Captcha;

use Manix\Brat\Components\Controller;

class CaptchaController extends Controller {

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
