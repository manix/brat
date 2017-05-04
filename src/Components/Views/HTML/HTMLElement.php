<?php

namespace Manix\Brat\Components\Views\HTML;

use Manix\Brat\Components\Views\View;
use Manix\Brat\Helpers\HTMLGenerator;

abstract class HTMLElement extends View {

  /**
   * An HTML Generator helper.
   * @var HTMLGenerator The helper.
   */
  protected $html;

  public function __construct($data, HTMLGenerator $html) {
    parent::__construct($data);

    $this->html = $html;
  }

  final protected function render() {
    ob_start();
    $this->html();
    return ob_get_clean();
  }

  abstract public function html();
}
