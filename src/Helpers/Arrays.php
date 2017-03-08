<?php

namespace Manix\Brat\Helpers;

class Arrays {

    public function prefixFilter(array &$array, $prefix, $unset = false) {
        $new = [];
        $len = strlen($prefix);

        foreach ($array as $key => $value) {
            if (strpos($key, $prefix) === 0) {
                $new[substr($key, $len)] = $value;

                if ($unset === true) {
                    unset($array[$key]);
                }
            }
        }
        return $new;
    }

    public function isNull($array) {
        foreach ($array as $value) {
            if ($value !== null) {
                return false;
            }
        }
        return true;
    }

    public function merge(array $a, array $b) {
        foreach ($b as $k => $v) {
            if (is_array($v) && isset($a[$k]) && is_array($a[$k])) {
                $a[$k] = $this->merge($a[$k], $v);
            } else {
                $a[$k] = $v;
            }
        }

        return $a;
    }


}
