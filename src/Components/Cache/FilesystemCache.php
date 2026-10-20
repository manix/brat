<?php

namespace Manix\Brat\Components\Cache;

use Exception;
use Manix\Brat\Components\Filesystem\Directory;
use Manix\Brat\Components\Filesystem\File;
use const DEBUG_MODE;

class FilesystemCache extends CacheGateway {

    protected $dir;

    public function __construct(Directory $dir) {
        $this->dir = $dir;
        
        parent::__construct();
    }

    public function key($key) {
        if ($this->dir->validatePath($key)) {
            return parent::key($key);
        }

        throw new Exception('Invalid path in cache entry key.', 500);
    }

    public function persist($key, $value, $ttl) {
        return (bool)file_put_contents(new File($this->dir . '/' . $this->key($key)), time() + $ttl . serialize($value));
    }

    public function retrieve($key) {
        try {
            $contents = file_get_contents(new File($this->dir . '/' . $this->key($key)));
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
            return unlink(new File($this->dir . '/' . $this->key($key)));
        } catch (Exception $ex) {
            if (DEBUG_MODE) {
                throw $ex;
            }
        }
    }

    public function clear($hard = false) {
        if ($hard) {
            $this->dir->delete(true);
        } else {
            $now = time();
            
            foreach ($this->dir->files() as $file) {
                $f = fopen($file, 'r');
                $due = fread($f, 10);
                fclose($f);

                if ($due < $now) {
                    unlink($file);
                }
            }
        }
    }

    // under an exclusive lock on the file: requests counting at the same time wait for each other
    public function increment($key, $ttl, $by = 1) {
        $f = fopen(new File($this->dir . '/' . $this->key($key)), 'c+');
        flock($f, LOCK_EX);
        $contents = stream_get_contents($f);
        $due = (int)substr($contents, 0, 10);

        // missing (an empty file) or expired
        if ($due < time()) {
            $due = time() + $ttl;
            $count = $by;
        } else {
            $count = (int)unserialize(substr($contents, 10)) + $by;
        }

        ftruncate($f, 0);
        rewind($f);
        fwrite($f, $due . serialize($count));
        fclose($f);

        return $count;
    }

    // under the file's lock, like increment()
    public function add($key, $value, $ttl) {
        $f = fopen(new File($this->dir . '/' . $this->key($key)), 'c+');
        flock($f, LOCK_EX);
        $added = (int)fread($f, 10) < time();

        if ($added) {
            ftruncate($f, 0);
            rewind($f);
            fwrite($f, (time() + $ttl) . serialize($value));
        }

        fclose($f);

        return $added;
    }

}
