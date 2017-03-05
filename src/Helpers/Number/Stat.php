<?php

namespace Manix\Brat\Helpers\Number;

class Stat extends Number {

    public function __toString() {
        if ($this->value > 1000) {
            $matrix = [null, 'K', 'M', 'B', 'T'];

            for ($i = 1e12; $i > 1; $i /= 1e3) {
                if ($this->value > $i) {
                    return round($this->value / $i, $this->round) . $matrix[log10($i) / 3];
                }
            }
        }

        return $this->value;
    }

}
