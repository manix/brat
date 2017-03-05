<?php

namespace Manix\Brat\Components\Filesystem;

use Exception;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class Directory extends Inode {

    public function __construct($path, $recursive = true) {
        parent::__construct($path);

        if (!is_dir($path)) {
            if (!mkdir($path, 0711, (bool)$recursive)) {
                throw new Exception('Can not create directory');
            }
        }

        $this->path = $path;
    }

    public function copy($destination) {
        $dir = opendir($this->path);

        while (false !== ( $file = readdir($dir))) {
            if (( $file != '.' ) && ( $file != '..' )) {
                (new Factory)->get($this->path . '/' . $file)->copy($destination . '/' . $file);
            }
        }
        closedir($dir);
    }

    public function contents($stopafter = null) {
        $results = array();
        $count = 1;
        $factory = new Factory();
        if (is_dir($this->path)) {
            $handler = opendir($this->path);

            while (($file = readdir($handler)) && ($stopafter === null || $count < $stopafter)) {
                if ($file != '.' && $file != '..') {
                    $results[] = $factory->get($this->path . '/' . $file);
                    $count++;
                }
            }
            closedir($handler);
        }
        return $results;
    }

    public function delete($contentsOnly = false) {
        foreach ($this->contents() as $inode) {
            $inode->delete(false);
        }

        if ($contentsOnly === false) {
            rmdir($this->path);
        }

        return true;
    }

    public function move($destination) {
        if (rename($this->path, $destination)) {
            $this->path = $destination;
        }

        return $this;
    }

    public function size() {
        $bytes = 0;
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->path));
        foreach ($iterator as $i) {
            $bytes += $i->getSize();
        }
        return $bytes;
    }

    public function count() {
        return iterator_count(new FilesystemIterator($this->path, FilesystemIterator::SKIP_DOTS));
    }

    public function map() {
        $map = $this->contents();

        foreach ($map as $elem) {
            if ($elem instanceof self) {
                $elem->nodes = $elem->map();
            }
        }

        return $map;
    }

    public function files() {
        $map = [];

        foreach ($this->contents() as $node) {
            if ($node instanceof self) {
                foreach ($node->files() as $path) {
                    $map[] = $path;
                }
            } else {
                $map[] = $node;
            }
        }

        return $map;
    }

}
