<?php

use Manix\Brat\Components\Translator;
use Manix\Brat\Components\Views\HTML\Provider;
use Manix\Brat\Components\Views\HTML\View;
use Manix\Brat\Program;

define('MANIX', 'MANIX');
define('SITE_DOMAIN', isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : null);
define('SITE_URL', SITE_DOMAIN === null ? null : (((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https://' : 'http://') . SITE_DOMAIN . substr($_SERVER['PHP_SELF'], 0, -10)));

$_ENV = array_merge($_ENV, require(PROJECT_PATH . '/.env.php'));

define('DEBUG_MODE', $_ENV['env'] === 'debug');

$loader = require(PROJECT_PATH . '/vendor/autoload.php');
$loader->addPsr4('Manix\\Brat\\', PROJECT_PATH . '/vendor/manix/src');

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

    use Translator;

    /**
     * Run the Manix project.
     * 
     * @param callable $error A function to pass to set_exception_handler
     * @param callable $respond A callable for the response generation.
     * @throws Exception
     */
    function run(Program $program) {
        set_exception_handler([$program, 'error']);

        $route = trim($_GET['route'] ?? null);

        if (!$route) {
            $route = 'index';
        }

        define('ROUTE', $route);

        $class = config('routes')[$route] ?? ($_ENV['projectNS'] . 'Controllers\\' . join('\\', array_map('ucfirst', explode('/', $route))));

        if (!loader()->loadClass($class)) {
            throw new Exception($this->t8('common', 'ctrlnotfound'), 404);
        }

        $controller = new $class();
        // TODO think about request-method
        $data = $controller->{strtolower($_POST['request-method'] ?? $_SERVER['REQUEST_METHOD'])}();

        $program->respond($data, $controller);
    }
};
