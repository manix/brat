<?php

namespace Manix\Brat\Components\Views\HTML;

abstract class HTMLDocument extends HTMLElement {

    /**
     * @var string The title of this resource.
     */
    public $title;

    /**
     * @var array An array of strings that represent the children of the head element in an HTML response.
     */
    protected $head = [];

    /**
     * Adds a script element to the document head.
     * 
     * @param string $src URL of the script resource.
     * @param array $attributes Attributes of the element.
     */
    public function addScript($src, array $attributes = []) {
        $this->head[] = $this->html->script($src, $attributes);
    }

    /**
     * Adds a link element to the document head.
     * 
     * @param string $href URL of the link resource.
     * @param array $attributes Attributes of the element.
     */
    public function addStyle($href, array $attributes = []) {
        $this->head[] = $this->html->css($href, $attributes);
    }

    /**
     * Adds arbitrary HTML to the head element.
     * 
     * @param string $html HTML string.
     */
    public function appendToHead($html) {
        $this->head[] = $html;
    }

    /**
     * Generates an HTML string representing the resource's HTML head element's children.
     * 
     * @return string HTML containing string.
     */
    public function head() {
        return
        $this->html->title($this->title) .
        implode('', $this->head);
    }

    abstract public function body();

    public function html() {
        ?>
        <!doctype html>
        <html>
            <head>
                <?= $this->head() ?>
            </head>
            <body>
                <?= $this->body() ?>
            </body>
        </html>
        <?php
    }

}
