<?php

namespace Manix\Brat\Components;

trait Translator {

    protected static $translatorStrings = [];

    /**
     * Caches a path so it does not have to be provided to t8 every time.
     * 
     * @param string $path Relative path to a lang file.
     */
    public function cacheT8($path) {
        $this->__t8path = $path;
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
            return $this->getTranslatedStrings($path)[$string] ?? ($path . ':' . $string);
        } else {
            return preg_replace_callback('/{\$(\d+)}/', function($match) use(&$data) {
                return $data[$match[1]] ?? null;
            }, $this->getTranslatedStrings($path)[$string] ?? ($path . ':' . $string));
        }
    }

    /**
     * Loads and returns translations in the program.
     * 
     * @param string $path Relative path to a lang file.
     * @return mixed null if file doesn't exist or file's return value otherwise.
     */
    protected function getTranslatedStrings($path) {
        /*
         * Call to constructAbsoluteLangFilePath() not assigned to variable
         * because if it is taken outside the if statement it will be called 
         * every time this function gets called, this way it only gets called 
         * if $translatorStrings is empty.
         */
        if (!isset(self::$translatorStrings[$path])) {
            self::$translatorStrings[$path] = is_file($this->constructAbsoluteLangFilePath($path)) ? require $this->constructAbsoluteLangFilePath($path) : [];
        }

        return self::$translatorStrings[$path];
    }

    /**
     * Constructs the absolute path to a lang file.
     * 
     * @param string $path The relative path to a lang file.
     * @return string Absolute path.
     */
    protected function constructAbsoluteLangFilePath($path) {
        return PROJECT_PATH . '/lang/' . LANG . '/' . $path . '.php';
    }

}
