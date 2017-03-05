<?php

namespace Manix\Brat\Components\Filesystem;

abstract class Inode {

    protected $path;

    abstract public function move($destination);

    abstract public function copy($destination);

    abstract public function delete();

    public function __construct($path) {
        if (!$this->validatePath($path)) {
            throw new Exception('Invalid path');
        }
    }

    public function __toString() {
        return $this->path;
    }

    public function validatePath($path) {
        return strpos($path, "\x00") === false;
    }

}
