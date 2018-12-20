<?php

namespace Manix\Brat\Components;

abstract class CriteriaInterpreter {

    abstract protected function eq($data, $value);

    abstract protected function noteq($data, $value);

    abstract protected function gt($data, $value);

    abstract protected function lt($data, $value);

    abstract protected function in($data, $list);

    abstract protected function notin($data, $list);

    abstract protected function btw($data, $values);

    abstract protected function notbtw($data, $values);

    abstract protected function like($data, $value);

    abstract protected function notlike($data, $value);
}
