<?php

namespace Manix\Brat\Components\Views\HTML;

use Manix\Brat\Components\Views\View as BaseView;
use Manix\Brat\Helpers\HTMLGenerator;

abstract class View extends BaseView {

    /**
     * An HTML Generator helper.
     * @var HTMLGenerator The helper.
     */
    protected $html;

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
        $attributes['src'] = $src;

        $this->head[] = $this->html->script(null, $attributes);
    }

    /**
     * Adds a link element to the document head.
     * 
     * @param string $href URL of the link resource.
     * @param array $attributes Attributes of the element.
     */
    public function addStyle($href, array $attributes = []) {
        $attributes['href'] = $href;
        $attributes['type'] = 'text/css';
        $attributes['rel'] = 'stylesheet';

        $this->head[] = $this->html->link($attributes);
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
    public function getHead() {
        return
        $this->html->title($this->title) .
        implode('', $this->head);
    }

    public function getPath() {
        return $this->path;
    }

    public function __construct($data, HTMLGenerator $html) {
        parent::__construct($data);

        $this->html = $html;
    }

    final protected function render() {
        ob_start();
        $this->html();
        return ob_get_clean();
    }

    abstract public function html();
}
