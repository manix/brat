<?php

namespace Manix\Brat\Components\Persistence\FS;

use Manix\Brat\Components\Criteria;
use Manix\Brat\Components\CriteriaInterpreter;

class FilesystemGatewayCriteriaInterpreter extends CriteriaInterpreter {

    protected $phpcode;

    public function __construct(Criteria $criteria) {
        $this->phpcode = $this->interpret($criteria);
    }

    protected function interpret(Criteria $criteria): string {
        $conditions = [];

        foreach ($criteria->rules() as $rule) {
            if ($rule instanceof Criteria) {
                $conditions[] = '(' . $this->interpret($rule) . ')';
            } else {
                foreach ($rule as $key => $data) {
                    $conditions[] = "\$this->{$key}(\$data['{$data[0]}'], " . var_export($data[1], true) . ')';
                }
            }
        }
        
        if (empty($conditions)) {
            $conditions[] = 1;
        }

        switch ($criteria->glue()) {
            case 'OR':
                $glue = ' || ';
                break;

            default:
                $glue = ' && ';
                break;
        }

        return implode($glue, $conditions);
    }

    public function validate($data) {
        return eval('return ' . $this->phpcode . ';');
    }

    protected function eq($data, $value) {
        return $data == $value;
    }

    protected function noteq($data, $value) {
        return $data != $value;
    }

    protected function gt($data, $value) {
        return $data > $value;
    }

    protected function lt($data, $value) {
        return $data < $value;
    }

    protected function in($data, $list) {
        return in_array($data, $list);
    }

    protected function notin($data, $list) {
        return !$this->in($data, $list);
    }

    protected function btw($data, $values) {
        return $data > $values[0] && $data < $values[1];
    }

    protected function notbtw($data, $values) {
        return !$this->btw($data, $values);
    }

    protected function like($data, $value) {
        return mb_strpos($data, $value) !== false;
    }

    protected function notlike($data, $value) {
        return !$this->like($data, $value);
    }

}
