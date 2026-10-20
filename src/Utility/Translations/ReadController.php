<?php

namespace Manix\Brat\Utility\Translations;

use Exception;
use Manix\Brat\Components\Controller;
use const PROJECT_PATH;

class ReadController extends Controller {

  public function get() {
    // the file is required: only a configured language and a lang file's name ("manix/util/users/common")
    $lang = $_GET['lang'] ?? null;
    $lang = is_string($lang) && isset(config('lang')['languages'][$lang]) ? $lang : lang();
    $name = $_GET['path'] ?? null;

    if (is_string($name) && preg_match('~^[\w-]+(/[\w-]+)*$~', $name)) {
      $path = PROJECT_PATH . '/lang/' . $lang . '/' . $name . '.php';

      if (is_file($path)) {
        return require($path);
      }
    }

    throw new Exception('Please specify path', 400);
  }

}
