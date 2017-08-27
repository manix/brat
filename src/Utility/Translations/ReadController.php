<?php

namespace Manix\Brat\Utility\Translations;

use Manix\Brat\Components\Controller;
use const LANG;
use const PROJECT_PATH;

class ReadController extends Controller {

  public $page = TranslationsScriptView::class;

  public function get() {
    if (isset($_GET['path'])) {
      $path = PROJECT_PATH . '/lang/' . (($_GET['lang'] ?? null) ? $_GET['lang'] : LANG) . '/' . str_replace('..', '', $_GET['path']) . '.php';

      if (is_file($path)) {
        return require($path);
      }
    }

    return [];
  }

}
