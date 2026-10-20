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

    /**
     * Adds $by to the counter under $key and returns the new count. A missing counter starts at 0 and lasts $ttl
     * seconds, counting doesn't extend it. Atomic: requests counting at the same time each get their own count.
     */
    abstract public function increment($key, $ttl, $by = 1);

    /**
     * Persists $value under $key for $ttl seconds only if $key is missing, and returns whether it did. Atomic: of
     * requests adding the same key at the same time, one does.
     */
    abstract public function add($key, $value, $ttl);

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
            $value = $callback($key, $ttl);
            $this->persist($key, $value, $ttl);
        }

        return $value;
    }

}
