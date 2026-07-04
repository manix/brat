<?php

namespace Manix\Brat\Components\Cache;

use Exception;
use Manix\Brat\Components\Filesystem\Directory;
use Manix\Brat\Components\Filesystem\File;
use const DEBUG_MODE;

class MemoryCache extends CacheGateway {

    protected $data = [];

    public function persist($key, $value, $ttl) {
        return $this->data[$this->key($key)] = time() + $ttl . serialize($value);
    }

    public function retrieve($key) {
        try {
            $contents = $this->data[$this->key($key)] ?? '0';
            $due = substr($contents, 0, 10);

            if ($due < time()) {
                $this->wipe($key);
                return null;
            } else {
                return unserialize(substr($contents, 10));
            }
        } catch (Exception $ex) {
            if (DEBUG_MODE) {
                throw $ex;
            } else {
                return null;
            }
        }
    }

    public function wipe($key) {
        try {
            unset($this->data[$this->key($key)]);
            return true;
        } catch (Exception $ex) {
            if (DEBUG_MODE) {
                throw $ex;
            }
        }
    }

    public function clear($hard = false) {
        if ($hard) {
            $this->data = [];
        } else {
            $now = time();
            
            foreach ($this->data as $key => $value) {
                $due = substr($value, 0, 10);

                if ($due < $now) {
                    unset($this->data[$key]);
                }
            }
        }
    }

}
