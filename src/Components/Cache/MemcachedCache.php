<?php

namespace Manix\Brat\Components\Cache;

use Memcached;

class MemcachedCache extends CacheGateway {

    /**
     * @var Memcached
     */
    protected $conn;

    public function __construct(Memcached $conn) {
        $this->conn = $conn;
        parent::__construct();
    }

    public function persist($key, $value, $ttl) {
        // don't use unix timestamp for ttl, apparently memcached's internal clock can lose sync
        // and cause keys to not be persisted 
        $this->conn->set($this->key($key), $value, $ttl);
    }

    public function retrieve($key) {
        $value = $this->conn->get($this->key($key));
        
        return $value === false ? null : $value;
    }

    public function wipe($key) {
        $this->conn->delete($this->key($key));
    }

    public function clear($hard = false) {
        if ($hard) {
            $this->conn->flush();
        }
    }

}
