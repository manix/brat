<?php

namespace Manix\Brat\Components;

use stdClass;

$translatorStrings = [];

trait Translator {

  // protected static $translatorStrings = [];

  // private $__t8path;

  /**
   * Caches a path so it does not have to be provided to t8 every time.
   *
   * @param string $path Relative path to a lang file.
   */
  public function cacheT8($path) {
    $this->__t8path = $path;
  }

  public function clearTranslationStrings() {
    global $translatorStrings;

    $translatorStrings = [];
  }

  /**
   * Get a translation.
   *
   * @param string $path Relative path to a lang file.
   * @param string $string Key for the translation in the lang file.
   * @return string The translated string.
   */
  public function t8($path, $string = null, $data = null) {
    /*
     * If string is missing then path must be loaded
     */
    if ($string === null) {
      $string = $path;
      $path = $this->__t8path ?? null;
    }

    if ($data === null) {
      return $this->normalizeString($path, $string, $data, $this->getTranslatedStrings($path)[$string] ?? ($path . ':' . $string));
    } else {
      $t8d = $this->normalizeString($path, $string, $data, $this->getTranslatedStrings($path)[$string] ?? null);
      return preg_replace_callback('/{\$(\d+)}/', function($match) use(&$data, $t8d) {
        $m = $data[$match[1]] ?? null;
        return $t8d ? $m : json_encode($m);
      }, $t8d ?? ($path . ':' . $string . ':[{$' . join('},{$', range(0, count($data) - 1)) . '}]'));
    }
  }

  protected function normalizeString($path, $string, $data, $translated) {
    if (is_callable($translated)) {
      return $translated($data);
    }
    return $translated;
  }

  /**
   * Loads and returns translations in the program.
   *
   * @param string $path Relative path to a lang file.
   * @return mixed null if file doesn't exist or file's return value otherwise.
   */
  protected function getTranslatedStrings($path) {
    global $translatorStrings;

    // keyed by language too, a file loaded before the language is known must not stick
    $lang = lang();

    if (!isset($translatorStrings[$lang][$path])) {
      $file = $this->constructAbsoluteLangFilePath($path);
      $translatorStrings[$lang][$path] = is_file($file) ? require $file : [];
    }

    return $translatorStrings[$lang][$path];
  }

  /**
   * Constructs the absolute path to a lang file.
   *
   * @param string $path The relative path to a lang file.
   * @return string Absolute path.
   */
  protected function constructAbsoluteLangFilePath($path) {
    return PROJECT_PATH . '/lang/' . lang() . '/' . $path . '.php';
  }

}

class TranslatorClass extends stdClass {
  use Translator;
}