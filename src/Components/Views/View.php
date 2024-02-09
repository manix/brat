<?php

namespace Manix\Brat\Components\Views;

use Manix\Brat\Components\Translator;
use stdClass;

abstract class View extends stdClass {

    use Translator;

    /**
     * @var mixed The data of the view.
     */
    public $data = [];

    public function __construct($data = []) {
        $this->data($data);
    }

    /**
     * Set data to the view.
     * @param mixed $data The new data.
     * @return $this
     */
    public function data($data) {
        $this->data = $data;
        return $this;
    }

    /**
     * Must return the data for render.
     */
    abstract protected function render();

    public function __toString() {
        try {
            return $this->render();
        } catch (Exception $e) {
            if (DEBUG_MODE) {
                return $this->t8('common', 'viewErrorRenderingD', [$e->getMessage()]);
            } else {
                return $this->t8('common', 'viewErrorRendering');
            }
        }
    }

}
