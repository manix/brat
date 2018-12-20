<?php

namespace Manix\Brat\Utility\Captcha;

use Manix\Brat\Components\Filesystem\Directory;
use Manix\Brat\Components\Translator;
use Manix\Brat\Helpers\HTMLGenerator;
use const MANIX;
use function route;

class CaptchaManager {

  use Translator;

  /**
   * Generate a random code of $length length.
   * @param int $length The length of the code.
   * @return string The generated code.
   */
  protected function generateCode(int $length = 6): string {
    return substr(str_shuffle('QWERTYUIOPASDFGHJKLZXCVBNM123456789'), 0, $length);
  }

  /**
   * Generates an image resource.
   * @param int $len Length of the captcha code.
   * @return resource
   */
  public function generateImage(int $len) {
    $code = $this->retrieveCode();

    if ($code === null || strlen($code) !== $len) {
      $code = $this->generateCode($len);
      $this->persistCode($code);
    }

    $width = $len * 51;

    $im = imagecreatetruecolor($width, 100);

    $white = imagecolorallocate($im, 255, 255, 255);
    $black = imagecolorallocate($im, 0, 0, 0);

    imagecolortransparent($im, $white);
    imagefilledrectangle($im, 0, 0, $width, 100, $white);

    $path = __DIR__ . '/fonts';
    $dir = new Directory($path);

    $fonts = [];
    foreach ($dir->contents() as $font) {
      $fonts[] = $font;
    }
    $flen = count($fonts) - 1;

    for ($i = 0; $i < $len; $i++) {
      $font = $fonts[mt_rand(0, $flen)];
      imagettftext($im, 50, mt_rand(-30, 30), 10 + $i * 50, mt_rand(60, 90), $black, $font, $code[$i]);
    }

    return $im;
  }

  /**
   * Retrieve the persisted code.
   * @return string The code.
   */
  protected function retrieveCode() {
    return $_SESSION[MANIX]['captcha'] ?? null;
  }

  /**
   * Persist the code somewhere.
   * @param string $code
   */
  protected function persistCode($code) {
    $_SESSION[MANIX]['captcha'] = $code;
  }

  /**
   * Expire an existing persisted code.
   */
  public function expire() {
    unset($_SESSION[MANIX]['captcha']);
  }

  /**
   * Create an <img/> tag. The src will be the captcha controller url.
   * @param HTMLGenerator $html
   * @param array $attributes
   * @return string The generated img tag.
   */
  public function generateImageTag(HTMLGenerator $html, array $attributes = []) {
    $url = route(CaptchaController::class);

    $attributes['onclick'] = 'this.src = "' . $url . '?cb="+Date.now();';
    $attributes['style'] = 'cursor:pointer;';
    $attributes['title'] = $this->t8('manix/util/captcha', 'clickToReload');

    return $html->img($url, 'Captcha', $attributes);
  }

  /**
   * Check whether $code is matching the persisted code.
   * @param string $code
   * @return mixed NULL if code is matching or an error message if not.
   */
  public function validate($code) {
    if ($this->retrieveCode() === strtoupper($code)) {
      return null;
    }

    return $this->t8('manix/util/captcha', 'invalidCode');
  }

}
