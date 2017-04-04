<?php

namespace Manix\Brat;

use Exception;
use Manix\Brat\Components\Cache\CacheGateway;
use Manix\Brat\Components\Cache\FilesystemCache;
use Manix\Brat\Components\Controller;
use Manix\Brat\Components\Filesystem\Directory;
use Manix\Brat\Components\Translator;
use Manix\Brat\Components\Views\JSONView;
use Manix\Brat\Components\Views\PlainTextView;
use Manix\Brat\Helpers\HTMLGenerator;
use Throwable;
use const DEBUG_MODE;
use const PROJECT_PATH;
use const SITE_DOMAIN;
use function config;
use function loader;

/**
 * The main class that defines the behaviour of your program.
 */
abstract class Program {

  use Translator;

  /**
   * @var array The sorted list of preferred response types.
   */
  protected $requested = [];

  public function __construct() {


    foreach (explode(',', $_SERVER['HTTP_ACCEPT'] ?? null) as $mediaRange) {

      $type = null;
      $qparam = null;

      $split = preg_split('/\s*;\s*/', $mediaRange);
      if (isset($split[0])) {
        $type = $split[0];
        if (isset($split[1])) {
          $qparam = $split[1];
        }
      }

      $q = (substr($qparam, 0, 2) == 'q=' ? floatval(substr($qparam, 2)) : 1) * 100;
      if ($q <= 0) {
        continue;
      }
      if (substr($type, -1) == '*') {
        $q -= 1;
      }
      if ($type[0] == '*') {
        $q -= 1;
      }

      while (isset($this->requested[$q])) {
        $q -= 1;
      }

      $this->requested[$q] = $type;
    }

    rsort($this->requested);
  }

  /**
   * Sets up and starts PHP's session mechanism.
   */
  public function startSession() {
    session_name('manix-sess');
    session_set_cookie_params(0, '/', SITE_DOMAIN, false, true);
    // session_set_save_handler(new class, true);
    session_start();
  }

  /**
   * This function defines how to respond to requests.
   * @param mixed $data The data returned by the controller.
   * @param Controller $controller The called controller.
   */
  public function respond($data, $controller) {

    foreach ($this->requested as $type) {
      switch ($type) {
        case 'application/*':
        case 'application/json':
          header('Content-Type: application/json');
          return new JSONView($data);

        case 'text/plain':
          header('Content-Type: text/plain');
          return new PlainTextView($data);

        case '*/*':
        case 'text/*':
        case 'text/html':
          header('Content-Type: text/html');
          return new $controller->page($data, new HTMLGenerator());
      }
    }

    return $this->t8('common', 'unsuppFormat');
  }

  /**
   * This function defines how your program reacts to errors. Essentially
   * this function will just get passed to set_exception_handler()
   * @param Throwable $t
   */
  public function error(Throwable $t) {
    if (DEBUG_MODE) {
      echo "Error {$t->getCode()}: {$t->getMessage()}";
    } else {
      echo "An error occured.";
    }
  }

  /**
   * Create and return a controller instance from a given route.
   * @param string $route
   * @return Controller
   * @throws Exception
   */
  public function createController(string $route): Controller {
    if (!$route) {
      $route = 'index';
    }

    $routes = config('routes');
    $derived = $route . '/';

    while (false !== ($derived = substr($derived, 0, strrpos($derived, '/')))) {

      $def = $routes[$derived] ?? null;

      if ($def !== null) {
        if (substr($def, -1) === '\\') {
          // this means $def is a namespace

          $len = strlen($derived);

          $remaining = substr($route, $len ? $len + 1 : 0);

          $class = $def . join('\\', array_map('ucfirst', explode('/', $remaining)));
        } else {
          $class = $def;
        }

        break;
      }
    }

    if (!loader()->loadClass($class)) {
      throw new Exception($this->t8('common', 'ctrlnotfound'), 404);
    }

    return new $class;
  }

  /**
   * Determine the route of the request.
   * @return string The route.
   */
  public function fetchRoute(): string {
    return trim($_GET['route'] ?? null);
  }

  /**
   * This function is used to define the default caching gateway for the application.
   * 
   * @return CacheGateway
   */
  public function constructCacheGateway(): CacheGateway {
    return new FilesystemCache(new Directory(PROJECT_PATH . '/files/cache'));
  }

  /**
   * Find the URL corresponding to a given route.
   * @param string $class Can be a FQCN or namespace.
   * @return string The URL at which $route can be accessed.
   */
  public function findURLTo($class) {
    /*
     * Store already requested destinations in an oddly named property 
     * so that they will not have to be resolved again in further calls.
     */
    if (!isset($this->_rtc581928)) {
      $this->_rtc581928 = [];
    }

    $rc = $this->_rtc581928;

    if (isset($rc[$class])) {
      $routes = [$rc[$class] => config('routes')[$rc[$class]]];
    } else {
      $routes = config('routes');
    }

    $uri = null;
    $depth = -1;
    $at = null;

    foreach ($routes as $route => $target) {
      if ($class === $target) {
        $at = $route;
        $uri = $route;

        break;
      } else if (strpos($class, $target) === 0 && substr($target, -1) === '\\') {
        $d = substr_count($target, '\\');

        if ($d > $depth) {
          $depth = $d;
          $at = $route;

          $uri = '';
          if ($route) {
            $uri = $route . '/';
          }
          $uri .= implode('/', array_map('lcfirst', explode('\\', substr($class, strlen($target)))));
        }
      }
    }

    if ($uri !== null) {
      $rc[$class] = $at;

      return SITE_URL . '/' . $uri;
    }
  }

}
