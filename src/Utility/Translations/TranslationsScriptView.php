<?php

namespace Manix\Brat\Utility\Translations;

use Manix\Brat\Components\Views\HTML\HTMLElement;
use function route;

class TranslationsScriptView extends HTMLElement {

  public function html() {
    $url = route(ReadController::class);

    require('script.js');
  }

}
