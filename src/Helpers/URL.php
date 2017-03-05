<?php

namespace Manix\Brat\Helpers;

class URL {

    protected $url;

    public function __construct($url) {
        $this->url = (string)$url;
    }

    public function __toString() {
        return $this->absolute();
    }

    public function validate() {
        return filter_var($this->url, FILTER_VALIDATE_URL);
    }

    public function absolute() {
        return strpos($this->url, '://') === false ? SITE_URL . $this->url : $this->url;
    }

}
