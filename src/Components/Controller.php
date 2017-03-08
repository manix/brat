<?php

namespace Manix\Brat\Components;

abstract class Controller {

    use Translator;

    /**
     * @var string Name of the page that this controller will render.
     */
    public $page;

    /**
     * @var string Name of the layout that this controller will render.
     */
    public $layout;

}
