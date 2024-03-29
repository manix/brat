<?php

use Manix\Brat\Components\Program;

//register_shutdown_function(function($time){
//    echo microtime(true) - $time;
//}, microtime(true));

define('MANIX', 'MANIX');
define('SITE_DOMAIN', isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : null);

if ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off')) {
  define('SITE_URL', SITE_DOMAIN === null ? null : ('https://' . SITE_DOMAIN . ($_SERVER['SERVER_PORT'] !== '443' ? (':' . $_SERVER['SERVER_PORT']) : '') . substr($_SERVER['PHP_SELF'], 0, -10)));
} else {
  define('SITE_URL', SITE_DOMAIN === null ? null : ('http://' . SITE_DOMAIN . ($_SERVER['SERVER_PORT'] !== '80' ? (':' . $_SERVER['SERVER_PORT']) : '') . substr($_SERVER['PHP_SELF'], 0, -10)));
}

define('DEBUG_MODE', $_ENV['env'] === 'debug');

$projectLoader = require_once(PROJECT_PATH . '/../vendor/autoload.php');
// require brat autoloader
if (is_file(__DIR__ . '/../vendor/autoload.php')) {
  $bratLoader = require_once(__DIR__ . '/../vendor/autoload.php');
}

function loader($type = null) {
  global $bratLoader, $projectLoader;
  switch ($type) {
    case 'brat': return $bratLoader;
    default: return $projectLoader;
  }
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
 * @param array $query Associative array containing query parameters.
 * @return string URL
 */
function route($to, array $query = []) {
  global $manix;

  $url = $manix->program()->findRouteTo($to);

  if (!empty($query)) {
    $url .= '?' . http_build_query($query);
  }

  return $url;
}

function html($string, $flag = ENT_QUOTES, $encoding = 'UTF-8') {
  return htmlspecialchars($string ?? '', $flag, $encoding);
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

/**
 * Send mail using SMTP. This method is chosen by default because it is believed to be
 * the most utilised and the most secure one.
 * @param mixed $to Can be just a string representing the address or an array with 2 elements - [address, name]
 * @param string $subject
 * @param string $message
 * @param callable $callable A callable that receives the mailer instance
 * before sending, so any custom modifications can be made there.
 * @return bool Whether message has been sent successfully or not.
 */
function email($to, $subject, $message, callable $callable = null) {
  global $manix;

  return $manix->program()->sendMail($to, $subject, $message, $callable);
}

$registry = [];

/**
 * Get/set a value from/in the program's global registry.
 * @param string $key
 * @param mixed $value
 * @return mixed The previously stored value under $key or null if missing.
 */
function registry($key, $value = null) {
  global $registry;

  if ($value === null) {
    return $registry[$key] ?? null;
  } else {
    $registry[$key] = $value;
  }
}

/**
 * Get or set the global language.
 * Replaces the LANG constant
 * @param string $lang
 * @return string The current global language
 */
function lang($lang = null) {
  if ($lang && array_key_exists($lang, config('lang')['languages'])) {
    registry('LANG', $lang);
    return $lang;
  }
  return registry('LANG') ?? config('lang')['default'];
}

/**
 * Get the currently executing program
 * @return Program
 */
function program() {
  global $manix;

  return $manix->program();
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
   * Run a program.
   * @param Program $program Your program.
   */
  function run(Program $program, $debug = false) {
    if ($debug) {
      error_reporting(E_ALL);
      ini_set('display_errors', 1);
    }

    $this->program = $program;

    set_exception_handler([$this->program, 'error']);

    $controller = $program->createController($program->determineRoute());
    $data = $program->executeController($controller);

    exit($program->respond($data));
  }
};
