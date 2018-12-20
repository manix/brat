<?php

namespace Manix\Brat\Components\Views;

class JSONView extends View {

    protected function render() {
        return json_encode($this->data);
    }

}
