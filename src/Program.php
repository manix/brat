<?php

namespace Manix\Brat;

use Exception;
use Manix\Brat\Components\Controller;
use Manix\Brat\Components\Translator;
use Manix\Brat\Components\Views\JSONView;
use Manix\Brat\Components\Views\PlainTextView;
use Manix\Brat\Helpers\HTMLGenerator;
use Throwable;
use const DEBUG_MODE;
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

        $class = config('routes')[$route] ?? (config('project')['namespace'] . 'Controllers\\' . join('\\', array_map('ucfirst', explode('/', $route))));

        if (!loader()->loadClass($class)) {
            throw new Exception($this->t8('common', 'ctrlnotfound'), 404);
        }

        return new $class();
    }

    /**
     * Determine the route of the request.
     * @return string The route.
     */
    public function fetchRoute(): string {
        $route = trim($_GET['route'] ?? null);

        if (!$route) {
            $route = 'index';
        }

        return $route;
    }

}
