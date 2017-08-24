<?php

namespace Manix\Brat\Components\Forms;

use JsonSerializable;
use Manix\Brat\Helpers\HTMLGenerator;

class FormInput implements JsonSerializable {

    use FormElement;

    public function __construct($name = null, $type = null, $value = null) {
        $this->attributes = [
            'name' => $name,
            'type' => $type,
            'value' => $value
        ];
    }

    public function toHTML(HTMLGenerator $html) {
        $attributes = $this->attributes;
        $value = $attributes['value'];
        $type = $attributes['type'];

        switch ($type) {
            case 'textarea':
                $value = $attributes['value'];
                unset($attributes['value']);
                unset($attributes['type']);
                return $html->textarea($value, $attributes);

            case 'select':
                if (!is_array($value)) {
                    $value = [];
                }
                $selected = isset($attributes['selected']) ? $attributes['selected'] : null;
                unset($attributes['value']);
                unset($attributes['type']);
                unset($attributes['selected']);
                return $html->select($value, $attributes, $selected);

            default:
                if ($attributes['type'] == 'password') {
                    unset($attributes['value']);
                }
                return $html->input($attributes['name'], $type, (string)$value, $attributes);
        }
    }

  public function jsonSerialize() {
    return $this->attributes;
  }

}
