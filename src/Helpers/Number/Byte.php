<?php

namespace Manix\Brat\Helpers\Number;

class Byte extends Number {

    public function __toString() {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        $bytes = max($this->value, 0);
        $pow = min(floor(($bytes ? log($bytes) : 0) / log(1024)), count($units) - 1);

        $bytes /= (1 << (10 * $pow));

        return round($bytes, $this->round) . ' ' . $units[$pow];
    }

}
