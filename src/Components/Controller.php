<?php

namespace Manix\Brat\Components;

use Exception;

abstract class Controller {

    use Translator;

    /**
     * @var string Name of the page that this controller will render.
     */
    public $page;

    /**
     * @var array An array of common data to return to each request.
     */
    protected $data = [];

    public function data() {
        return $this->data;
    }

    public function __call($name, $arguments) {
        if (isset($name) && isset($arguments)) {
            throw new Exception('Method not found.', 404);
        }
    }

}
