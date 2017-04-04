<?php

use Manix\Brat\Program;

//register_shutdown_function(function($time){
//    echo microtime(true) - $time;
//}, microtime(true));

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

/**
 * Generate a URL from a namespace or controller class.
 * @param string $to Namespace or controller class name.
 * @return string URL
 */
function route($to) {
  global $manix;
  return $manix->program()->findURLTo($to);
}

function html($string, $flag = ENT_QUOTES, $encoding = 'UTF-8') {
  return htmlspecialchars($string, $flag, $encoding);
}

$cache = null;

/**
 * A complex function for working with cache. 
 * 
 * @param string $key The key for the cached item.
 * @param type $value A value that must be persisted or a callable that returns a default value for a read.
 * @param int $ttl Time to live measured in seconds.
 * 
 * @return mixed If no parameters are provided then the CacheGateway is returned. If only $key is provided or $key and $value are provided but $valeu is callable, then the item corresponding to $key will be returned and if there is nothing found $value will be invoked and its return value will be stored under $key and will be returned. If $key and $value are provided and $value is not callable then $value will be stored under $key for $ttl seconds.
 */
function cache(string $key = null, $value = null, int $ttl = 600) {
  global $cache;

  if ($cache === null) {
    global $manix;
    $cache = $manix->program()->constructCacheGateway();
  }

  if (!$key) {
    return $cache;
  }

  $callable = is_callable($value);
  if ($value === null || $callable) {
    if ($callable) {
      return $cache->magic($key, $ttl, $value);
    }
    return $cache->retrieve($key);
  }

  return $cache->persist($key, $value, $ttl);
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

$manix = new class {

  /**
   * @var Program The last ran program.
   */
  protected $program;

  /**
   * Get the last ran program.
   * @return Program
   */
  public function program(): Program {
    return $this->program;
  }

  /**
   * Run a Manix program.
   * @param Program $program Your program.
   */
  function run(Program $program) {
    $this->program = $program;

    set_exception_handler([$program, 'error']);
    $program->startSession();

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

    $controller = $program->createController($program->fetchRoute());

    $data = $controller->{strtolower($_POST['manix-method'] ?? $_SERVER['REQUEST_METHOD'])}();

    if (is_array($data)) {
      $data = array_merge($controller->data(), $data);
    }

    exit($program->respond($data, $controller));
  }
};
