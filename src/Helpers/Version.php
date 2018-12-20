<?php

namespace Manix\Brat\Helpers;

use Exception;
use JsonSerializable;

class Version implements JsonSerializable {

    protected $major;
    protected $minor;
    protected $patch;

    public function __construct($version, $minor = null, $patch = null) {
        if ($minor !== null && $patch !== null) {
            $elements = [$version, $minor, $patch];
        } else {
            $elements = explode('.', $version);

            if (count($elements) !== 3) {
                throw new Exception('Invalid version string.', 500);
            }
        }

        $this->major = (int)$elements[0];
        $this->minor = (int)$elements[1];
        $this->patch = (int)$elements[2];
    }

    public function __toString() {
        return $this->getString();
    }

    public function getMajor() {
        return $this->major;
    }

    public function getMinor() {
        return $this->minor;
    }

    public function getPatch() {
        return $this->patch;
    }

    public function getString() {
        return $this->major . '.' . $this->minor . '.' . $this->patch;
    }

    public function compare(Version $version) {
        foreach (['major', 'minor', 'patch'] as $element) {
            $result = $this->compareElement($version, $element);

            if ($result !== 0) {
                return $result;
            }
        }

        return 0;
    }

    public function isGreater(Version $version) {
        return $this->compare($version) === 1;
    }

    public function isLesser(Version $version) {
        return $this->compare($version) === -1;
    }

    public function isEqual(Version $version) {
        return $this->compare($version) === 0;
    }

    public function isCompatible(Version $version) {
        return $this->compareElement($version, 'major') === 0;
    }

    protected function compareElement(Version $version, $element) {
        if ($this->$element > $version->$element) {
            return 1;
        } elseif ($this->$element < $version->$element) {
            return -1;
        } else {
            return 0;
        }
    }

    public function jsonSerialize() {
        return $this->getString();
    }

}
