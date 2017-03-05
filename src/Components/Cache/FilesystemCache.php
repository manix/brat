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
        return (bool)file_put_contents(new File($this->dir . '/' . $this->key($key)), $_SERVER['REQUEST_TIME'] + $ttl . serialize($value));
    }

    public function retrieve($key) {
        try {
            $contents = file_get_contents(new File($this->dir . '/' . $this->key($key)));
            $due = substr($contents, 0, 10);

            if ($due < $_SERVER['REQUEST_TIME']) {
                $this->wipe($key);
                return false;
            } else {
                return unserialize(substr($contents, 10));
            }
        } catch (Exception $ex) {
            if (DEBUG_MODE) {
                throw $ex;
            } else {
                return false;
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
            foreach ($this->dir->files() as $file) {
                $f = fopen($file, 'r');
                $due = fread($f, 10);
                fclose($f);

                if ($due < $_SERVER['REQUEST_TIME']) {
                    unlink($file);
                }
            }
        }
    }

}
