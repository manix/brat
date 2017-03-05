<?php

namespace Manix\Brat;

use Manix\Brat\Components\Translator;
use Manix\Brat\Components\Views\JSONView;
use Manix\Brat\Components\Views\PlainTextView;
use Manix\Brat\Helpers\HTMLGenerator;
use Throwable;

abstract class Program {

    use Translator;

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

    public function respond($data, $controller) {

        foreach ($this->requested as $type) {
            switch ($type) {
                case 'application/*':
                case 'application/json':
                    header('Content-Type: application/json');
                    $view = new JSONView($data);
                    break;

                case 'text/plain':
                    header('Content-Type: text/plain');
                    $view = new PlainTextView($data);
                    break;

                case '*/*':
                case 'text/*':
                case 'text/html':
                    header('Content-Type: text/html');

                    $view = new $controller->layout([
                        'controller' => $controller,
                        'data' => $data
                    ], new HTMLGenerator());

                    break;
            }

            if (isset($view)) {
                exit($view);
            }
        }

        exit($this->t8('common', 'unsuppFormat'));
    }

    public function error(Throwable $t) {
        if (DEBUG_MODE) {
            echo "Error {$t->getCode()}: {$t->getMessage()}";
        } else {
            echo "An error occured.";
        }
    }

}
