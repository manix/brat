<?php

namespace Manix\Brat\Utility\HTTP;

use Error;
use Exception;
use Manix\Brat\Components\Controller;
use Manix\Brat\Components\Program;
use Manix\Brat\Components\Translator;
use Manix\Brat\Components\Views\JSONView;
use Manix\Brat\Components\Views\PlainTextView;
use Manix\Brat\Helpers\HTMLGenerator;
use SessionHandler;
use SessionHandlerInterface;
use const SITE_DOMAIN;
use const SITE_URL;
use function config;
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
      $qparam = '';

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
    if (!session_id()) {
      session_name('manix-sess');
      session_set_cookie_params(0, '/', SITE_DOMAIN, false, true);
      session_set_save_handler($this->createSessionHandler(), true);
      session_start();
    }
  }

  /**
   * Instantiate a session handler.
   * @return SessionHandlerInterface
   */
  protected function createSessionHandler(): SessionHandlerInterface {
    return new SessionHandler();
  }

  public function executeController(Controller $controller, $method = null) {
    $controller->addMiddleware('session', 'CSRF', 'query', 'lang');

    return parent::executeController($controller, $method);
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

    try {
      return new $class;
    } catch (Error $err) {
      if ($err->getMessage() === "Class '$class' not found") {
        throw new Exception($this->t8('common', 'ctrlnotfound'), 404);
      }
      throw $err;
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
