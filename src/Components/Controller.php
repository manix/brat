<?php

namespace Manix\Brat\Components;

use Manix\Brat\Components\Cache\CacheGateway;
use Manix\Brat\Components\Cache\FilesystemCache;
use Manix\Brat\Components\Filesystem\Directory;
use function config;

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

    /**
     * @var CacheGateway
     */
    protected $cache;

    public function __construct() {
        
    }

    /**
     * Creates an instance of CacheGateway for the controller.
     * @return CacheGateway
     */
    public function createCacheGateway(): CacheGateway {
        return new FilesystemCache(new Directory(config('cache')['fs']['path']));
    }

    /**
     * Retrieves the CacheGateway for this controller.
     * @return CacheGateway
     */
    final public function getCacheGateway(): CacheGateway {
        if ($this->cache === null) {
            $this->cache = $this->createCacheGateway();
        }

        return $this->cache;
    }

}
