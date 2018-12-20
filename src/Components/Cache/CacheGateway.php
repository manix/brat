<?php

namespace Manix\Brat\Components\Cache;

/**
 * A caching gateway.
 * 
 * @author Manix
 */
abstract class CacheGateway {

    /**
     * Persists $value under $key in the cache for $ttl seconds.
     * $value MUST NOT be false.
     */
    abstract public function persist($key, $value, $ttl);

    /**
     * Retrieves a persisted value from the cache.
     */
    abstract public function retrieve($key);

    /**
     * Wipe a cached item.
     */
    abstract public function wipe($key);

    /**
     * Clean the cache from expired items. If $hard is true then wipe all items.
     */
    abstract public function clear($hard = false);

    public function __construct() {
        $gc = config('cache')['gc'];
        
        if (mt_rand(0, $gc['divisor']) <= $gc['probability']) {
            $this->clear();
        }
    }

    /**
     * Get prefixed key.
     * @param string $key The key.
     * @return string The prefixed key.
     */
    public function key($key) {
        return (config('cache')['prefix'] ?? null) . $key;
    }

    /**
     * Extend the ttl of an item.
     * @param string $key The key.
     * @param int $ttl New ttl.
     * @return bool Status.
     */
    public function extend($key, $ttl) {
        return $this->persist($key, $this->retrieve($key), $ttl);
    }

    public function has($key) {
        return $this->retrieve($key) !== null;
    }

    public function magic($key, $ttl, callable $callback) {
        $value = $this->retrieve($key);

        if ($value === null) {
            $value = $callback();
            $this->persist($key, $value, $ttl);
        }

        return $value;
    }

}
