<?php

use Manix\Brat\Program;

define('MANIX', 'MANIX');
define('SITE_DOMAIN', isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : null);
define('SITE_URL', SITE_DOMAIN === null ? null : (((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https://' : 'http://') . SITE_DOMAIN . substr($_SERVER['PHP_SELF'], 0, -10)));

$_ENV = array_merge($_ENV, require(PROJECT_PATH . '/.env.php'));

define('DEBUG_MODE', $_ENV['env'] === 'debug');

$loader = require(PROJECT_PATH . '/../vendor/autoload.php');

function loader() {
    global $loader;
    return $loader;
}

$protocol = null;

function protocol() {
    global $protocol;
    if (!$protocol) {
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https://' : 'http://';
    }
    return $protocol;
}

$url = null;

function url() {
    global $url;
    if (!$url) {
        $url = protocol() . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
    }
    return $url;
}

function html($string, $flag = ENT_QUOTES, $encoding = 'UTF-8') {
    return htmlspecialchars($string, $flag, $encoding);
}

$settings = [];

function config($file) {
    global $settings;

    if (!isset($settings[$file])) {
        $path = PROJECT_PATH . '/config/' . $file . '.php';

        if (!is_file($path)) {
            throw new Exception('Attempt to load inexistent config file.', 500);
        }

        $settings[$file] = require($path);
    }

    return $settings[$file];
}

if (!isset($_SESSION[MANIX]['lang'])) {
    $lc = config('lang');

    if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        $langs = $lc['languages'];

        $q = 0;
        foreach (explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']) as $part) {
            list($code, $priority) = explode(';q=', $part . ';q=');

            if (isset($langs[$code])) {
                if (!isset($priority)) {
                    $priority = 1;
                }

                if ($priority > $q) {
                    $lang = $code;
                    $q = $priority;
                }
            }
        }

        $_SESSION[MANIX]['lang'] = $lang;
    } else {
        $_SESSION[MANIX]['lang'] = $lc['default'];
    }
}

define('LANG', $_SESSION[MANIX]['lang']);

$manix = new class {

    /**
     * Run a Manix program.
     * @param Program $program Your program.
     */
    function run(Program $program) {
        set_exception_handler([$program, 'error']);

        $route = $program->fetchRoute();
        $controller = $program->createController($route);
        $data = $controller->{strtolower($_POST['manix-method'] ?? $_SERVER['REQUEST_METHOD'])}();

        exit($program->respond($data, $controller));
    }
};
