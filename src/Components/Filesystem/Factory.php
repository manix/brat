<?php

namespace Manix\Brat\Components\Filesystem;

use Exception;

class Factory {

    public function get($path) {
        if (is_dir($path)) {
            return new Directory($path);
        } elseif (is_file($path)) {
            return new File($path);
        }

        throw new Exception('Non-existent inode');
    }

}
