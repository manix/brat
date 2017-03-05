<?php

namespace Manix\Brat\Components\Views\HTML;

use Manix\Brat\Components\Cache\CacheGateway;
use Manix\Brat\Helpers\HTMLGenerator;
use const DEBUG_MODE;

class Provider {

    protected $cache;
    protected $html;

    public function __construct(CacheGateway $gate, HTMLGenerator $html) {
        $this->cache = $gate;
        $this->html = $html;
    }

    public function get(string $path) {
        if (DEBUG_MODE) {
            return file_get_contents($path);
        } else {
            return $this->cache->magic('views:' . $path, 3600, function() use($path) {
                return $this->html->minify(shell_exec('php -w ' . $path));
            });
        }
    }

    /**
     * Get the provider HTML generator.
     * @return HTMLGenerator
     */
    public function html() : HTMLGenerator {
        return $this->html;
    }
}
