<?php

namespace Manix\Brat\Components\Filesystem;

use Exception;
use FilesystemIterator;
use Generator;
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

    /**
     * Copy a directory recursively.
     * @param string $destination
     */
    public function copy($destination) {
        $dir = opendir($this->path);

        while (false !== ( $file = readdir($dir))) {
            if (( $file != '.' ) && ( $file != '..' )) {
                (new Factory)->get($this->path . '/' . $file)->copy($destination . '/' . $file);
            }
        }
        closedir($dir);
    }

    /**
     * Get the contents of this directory.
     * @param bool $skipdots Whether to skip hidden files (starting with a dot).
     * @return Generator
     */
    public function contents($skipdots = true) {
        $factory = new Factory();
        if (is_dir($this->path)) {
            $handler = opendir($this->path);

            while (($file = readdir($handler))) {
                if ($file[0] === '.' && $skipdots) {
                    continue;
                }

                yield $factory->get($this->path . '/' . $file);
            }
            closedir($handler);
        }
    }

    /**
     * Delete a directory recursively.
     * @param bool $contentsOnly If set to true then only the contents of this directory will be deleted, but the directory itself will remain.
     */
    public function delete($contentsOnly = false): bool {
        foreach ($this->contents() as $inode) {
            return $inode->delete(false);
        }

        if ($contentsOnly === false) {
            return rmdir($this->path);
        }
    }

    /**
     * Move the directory.
     * @param string $destination
     * @return $this
     */
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

    public function map($skipdots = true) {
        foreach ($this->contents($skipdots) as $elem) {
            if ($elem instanceof self) {
                $elem->nodes = $elem->map();
            }

            yield $this;
        }
    }

    public function files($skipdots = true) {
        foreach ($this->contents($skipdots) as $node) {
            if ($node instanceof self) {
                foreach ($node->files($skipdots) as $path) {
                    yield $path;
                }
            } else {
                yield $node;
            }
        }
    }

}
