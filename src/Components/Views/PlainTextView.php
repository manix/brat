<?php

namespace Manix\Brat\Components\Views;

class PlainTextView extends View {
    
    protected function render() {
        return print_r($this->data, true);
    }

}
