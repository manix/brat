<?php

namespace Manix\Brat\Components\Filesystem;

use Exception;

class File extends Inode {

  public function __construct($path, $recursive = true) {
    parent::__construct($path);

    if (!is_file($path) && $recursive) {
      new Directory(dirname($path));

      if (!touch($path)) {
        throw new Exception('Can not touch file.');
      }
    }

    $this->path = $path;
  }

  public function copy($destination) {
    if (!copy($this->path, $destination)) {
      throw new Exception('Can not copy file');
    }

    return new self($destination);
  }

  public function delete(): bool {
    if (!unlink($this->path)) {
      throw new Exception('Can not delete file');
    }

    return true;
  }

  public function move($destination) {
    if (rename($this->path, $destination)) {
      $this->path = $destination;
    }

    return $this;
  }

}
