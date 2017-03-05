<?php

namespace Manix\Brat\Components\Views;

use Manix\Brat\Components\Translator;

abstract class View {

    use Translator;

    /**
     * @var mixed The data of the view.
     */
    public $data = [];

    public function __construct($data) {
        $this->data = $data;
    }
    
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
