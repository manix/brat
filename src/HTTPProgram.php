<?php

namespace Manix\Brat;

use Manix\Brat\Components\Controller;
use Manix\Brat\Components\Program;
use Manix\Brat\Components\Translator;
use Manix\Brat\Components\Views\JSONView;
use Manix\Brat\Components\Views\PlainTextView;
use Manix\Brat\Helpers\HTMLGenerator;
use Manix\Brat\Utility\Events\Controllers\BeforeExecute;
use Exception;
use SessionHandler;
use SessionHandlerInterface;
use const CSRF_TOKEN;
use const MANIX;
use const SITE_DOMAIN;
use const SITE_URL;
use function config;
use function loader;
use function registry;

/**
 * The main class that defines the behaviour of your program.
 */
class HTTPProgram extends Program {

  use Translator;

  /**
   * @var array The sorted list of preferred response types.
   */
  protected $requested = [];
  /**
   * @var array Stores the resolved class-to-url results.
   */
  protected $resolvedURLs = [];

  public function __construct() {

    foreach (explode(',', $_SERVER['HTTP_ACCEPT'] ?? '*/*') as $mediaRange) {

      /*
       * Determine the requested response type.
       */
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

    /*
     * Start the session
     */
    $this->startSession();

    /*
     * Generate a CSRF token
     */
    if (!isset($_SESSION[MANIX]['csrf'])) {
      $_SESSION[MANIX]['csrf'] = $this->generateCSRFToken(32);
    }

    if (!defined('CSRF_TOKEN')) {
      define('CSRF_TOKEN', $_SESSION[MANIX]['csrf']);
    }

    /*
     * Determine language and store in session.
     */
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

            if ($priority >= $q) {
              $lang = $code;
              $q = $priority;
            }
          }
        }

        $_SESSION[MANIX]['lang'] = $lang ?? $lc['default'];
      } else {
        $_SESSION[MANIX]['lang'] = $lc['default'];
      }
    }

    if (!defined('LANG')) {
      define('LANG', $_SESSION[MANIX]['lang']);
    }
  }

  /**
   * Sets up and starts PHP's session mechanism.
   */
  public function startSession() {
    session_name('manix-sess');
    session_set_cookie_params(0, '/', SITE_DOMAIN, false, true);
    session_set_save_handler($this->createSessionHandler(), true);
    session_start();
  }

  /**
   * Instantiate a session handler.
   * @return SessionHandlerInterface
   */
  protected function createSessionHandler(): SessionHandlerInterface {
    return new SessionHandler();
  }

  /**
   * Generates a random token.
   * @param int $length Length of the generated token.
   * @return string The token.
   */
  protected function generateCSRFToken($length) {
    $token = '';

    for ($i = 0; $i < $length; $i++) {
      $token .= mt_rand(0, 9);
    }

    return $token;
  }

  /**
   * This function defines how to respond to requests.
   * @param mixed $data The data returned by the controller.
   */
  public function respond($data) {
    $page = registry('page');

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
          return new $page($data, new HTMLGenerator());
      }
    }

    return $this->t8('common', 'unsuppFormat');
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
    
    $controller = new $class;

    $controller->on(BeforeExecute::class, function($event) {
      $this->validateSession();

      if (in_array($event->getMethod(), $event->getController()->csrf())) {
        $this->validateCSRF();
      }
    });

    return $controller;
  }

  protected function validateSession() {
    $fingerprint = md5($_SERVER['HTTP_USER_AGENT'] ?? null);

    if (empty($_SESSION[MANIX]['fp'])) {
      $_SESSION[MANIX]['fp'] = $fingerprint;
    } else if ($fingerprint !== $_SESSION[MANIX]['fp']) {
      throw new Exception('Invalid session fingerprint.', 400);
    }
  }

  protected function validateCSRF() {
    $token = $_POST['manix-csrf'] ?? $_SERVER['HTTP_CSRF_TOKEN'] ?? null;

    if (CSRF_TOKEN !== $token) {
      throw new \Exception('CSRF token mismatch.', 400);
    }
  }

  /**
   * Determine the route of the request.
   * @return string The route.
   */
  public function determineRoute(): string {
    return trim($_GET['route'] ?? null);
  }

  /**
   * Determine the method that must be called on the controller.
   * @param Controller $controller
   * @return string
   */
  public function determineMethod(): string {
    return strtolower($_POST['manix-method'] ?? $_SERVER['REQUEST_METHOD'] ?? 'get');
  }

  /**
   * Find the URL corresponding to a given controller.
   * @param string $class Can be a FQCN or namespace.
   * @return string The URL at which the class can be accessed.
   */
  public function findRouteTo($class) {
    $rc = $this->resolvedURLs;

    /*
     * If this class has already been resolved then create a fake array of 
     * routes containing only the resolved one which will be returned below.
     */
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
