<?php

namespace Manix\Brat\Utility\Captcha;

use Manix\Brat\Components\Views\HTML\HTMLElement;

class CaptchaView extends HTMLElement {

  public function html() {
    header('Content-Type: image/png');
    imagepng($this->data);
  }

}
