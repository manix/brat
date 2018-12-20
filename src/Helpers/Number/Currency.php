<?php

namespace Manix\Brat\Helpers\Number;

class Currency extends Number {

    /**
     * @var string A symbol to show alongside the currency
     */
    public $symbol = '';

    /**
     * @var bool Whether to display the symbol before or after the currency
     */
    public $before = true;

    /**
     * @var string Decimal separator
     */
    public $decimal = '.';

    /**
     * @var string Thousands separator
     */
    public $thousand = ' ';

    /**
     * @var int Where to cut off the currency after the decimal point. This must be cohered with the $round property.
     */
    public $cut = 2;

    public function __toString() {
        $format = number_format(round($this->value, (int)$this->round), (int)$this->cut, $this->decimal, $this->thousand);

        return $this->before ? $this->symbol . $format : $format . $this->symbol;
    }

}
