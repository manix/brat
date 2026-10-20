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

    /**
     * Whether the URL stays on this site, so a redirect target taken from the request (the login's "b") can't send
     * the user elsewhere: an address under SITE_URL or a path from the root.
     */
    public function isLocal() {
        // SITE_URL must end there or go on with a path, a query or a fragment: "https://site.evil" is not under "https://site"
        if (strncmp($this->url, SITE_URL, strlen(SITE_URL)) === 0) {
            $next = $this->url[strlen(SITE_URL)] ?? '/';
            return $next === '/' || $next === '?' || $next === '#';
        }

        // browsers read "//host" and "/\host" as another host, and drop tabs and newlines before reading it
        return preg_match('~^/(?![/\\\\])[^\x00-\x1f\x7f]*$~', $this->url) === 1;
    }

}
