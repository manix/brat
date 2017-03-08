<?php

namespace Manix\Brat\Components;

class Criteria {

    protected $glue = 'AND';
    protected $rules = [];

    /**
     * Get the glue of this criteria.
     * @return string The glue.
     */
    public function glue() {
        return $this->glue;
    }

    /**
     * Get the rules of this criteria.
     * @return array The structured rules defined by this criteria.
     */
    public function rules() {
        return $this->rules;
    }

    public function __construct($glue = null) {
        if ($glue !== null) {
            $this->glue = strtoupper($glue);
        }
    }

    /**
     * Declare an equals rule. Must pass if given data is losely equal to $value.
     * @param string $property The key in the tested set.
     * @param mixed $value
     * @return $this
     */
    public function equals($property, $value) {
        $this->rules[] = ['eq' => [$property, $value]];
        return $this;
    }

    /**
     * Declare a not-equals rule. Must pass if given data is different from $value.
     * @param string $property The key in the tested set.
     * @param mixed $value
     * @return $this
     */
    public function notequals($property, $value) {
        $this->rules[] = ['noteq' => [$property, $value]];
        return $this;
    }

    /**
     * Declare a greater than rule. Must pass if given data is greater than $value.
     * @param string $property The key in the tested set.
     * @param mixed $value
     * @return $this
     */
    public function greater($property, $value) {
        $this->rules[] = ['gt' => [$property, $value]];
        return $this;
    }

    /**
     * Declare a less than rule. Must pass if given data is less than $value.
     * @param string $property The key in the tested set.
     * @param mixed $value
     * @return $this
     */
    public function less($property, $value) {
        $this->rules[] = ['lt' => [$property, $value]];
        return $this;
    }

    /**
     * Declare an in rule. Must pass if given data is contained within $values.
     * @param string $property The key in the tested set.
     * @param mixed $values
     * @return $this
     */
    public function in($property, array $values) {
        $this->rules[] = ['in' => [$property, $values]];
        return $this;
    }

    /**
     * Declare an in rule. Must pass if given data is not contained within $values.
     * @param string $property The key in the tested set.
     * @param mixed $values
     * @return $this
     */
    public function notin($property, array $values) {
        $this->rules[] = ['notin' => [$property, $values]];
        return $this;
    }

    /**
     * Declare a between rule. Must pass if given data is greater than $start and less than $end.
     * @param string $property The key in the tested set.
     * @param mixed $start The lower boundary.
     * @param mixed $end The upper boundary.
     * @return $this
     */
    public function between($property, $start, $end) {
        $this->rules[] = ['btw' => [$property, [$start, $end]]];
        return $this;
    }

    /**
     * Declare a not-between rule. Must pass if given data is less than $start or greater than $end.
     * @param string $property The key in the tested set.
     * @param mixed $start The lower boundary.
     * @param mixed $end The upper boundary.
     * @return $this
     */
    public function notbetween($property, $start, $end) {
        $this->rules[] = ['notbtw' => [$property, [$start, $end]]];
        return $this;
    }

    /**
     * Declare a like rule. Must pass if given data is contained anywhere within $value.
     * @param string $property The key in the tested set.
     * @param string $value
     * @return $this
     */
    public function like($property, $value) {
        $this->rules[] = ['like' => [$property, $value]];
        return $this;
    }

    /**
     * Declare a not-like rule. Must pass if given data is not contained anywhere within $value.
     * @param string $property The key in the tested set.
     * @param string $value
     * @return $this
     */
    public function notlike($property, $value) {
        $this->rules[] = ['notlike' => [$property, $value]];
        return $this;
    }

    /**
     * Declare a sub-group of rules in this criteria. This method returns a new Criteria instance.
     * @param string $glue The logical glue for the new Criteria.
     * @return Criteria
     */
    public function group($glue) {
        $criteria = new static($glue);

        $this->rules[] = $criteria;

        return $criteria;
    }

}
