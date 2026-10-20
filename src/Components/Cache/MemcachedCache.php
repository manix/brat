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

    // memcached's increment() doesn't create the key: a missing one is added with the count, and counted on if
    // another request added it in between
    public function increment($key, $ttl, $by = 1) {
        $key = $this->key($key);
        $count = $this->conn->increment($key, $by);

        if ($count === false) {
            $count = $this->conn->add($key, $by, $ttl) ? $by : $this->conn->increment($key, $by);
        }

        return (int)$count;
    }

    public function add($key, $value, $ttl) {
        return $this->conn->add($this->key($key), $value, $ttl);
    }

}
