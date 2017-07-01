<?php

namespace Manix\Brat\Components\Forms;

use Manix\Brat\Components\Validation\Ruleset;
use Manix\Brat\Helpers\HTMLGenerator;
use Manix\Brat\Helpers\URL;

class Form {

  use FormElement;

  /**
   * @var array An array of inputs for this form.
   */
  protected $inputs = [];

  /**
   * @var array An associative array containing the errors for this form.
   * Keys correspond to input names and values represent error messages.
   */
  public $errors = [];
  
  /**
   * Add an input element to the form.
   * @param string $name The name attribute of the input element
   * @param string $type The type attribute of the input element
   * @param string $value The value of the input element
   * @return FormInput
   */
  public function add($name, $type = null, $value = null) {
    $input = new FormInput($name, $type, $value);

    $this->inputs[$name] = $input;

    return $input;
  }

  public function addCollection($key, callable $callable) {
    $form = $callable(new self);

    foreach ($form->inputs as $name => $input) {
      $real = $this->add($key . '[' . $name . ']');

      foreach ($input->getAttributes() as $property => $value) {
        if ($property === 'name') {
          continue;
        }

        $real->setAttribute($property, $value);
      }
    }

    return $this;
  }

  /**
   * Remove an input element from this form.
   * @param string $name
   */
  public function remove(string $name) {
    unset($this->inputs[$name]);
    return $this;
  }

  /**
   * Check whether this form has an input element named $name.
   * @param string $name
   * @return bool
   */
  public function has(string $name): bool {
    return isset($this->inputs[$name]);
  }

  /**
   * Get input element by name.
   * @return FormInput
   */
  public function input($name) {
    return $this->inputs[$name];
  }

  /**
   * Get all inputs from this form.
   * @return FormInput[]
   */
  public function inputs() {
    return $this->inputs;
  }

  public function __get($name) {
    return $this->inputs[$name];
  }

  /**
   * Set the method attribute for this form.
   * @param type $method
   * @return $this
   */
  public function setMethod(string $method) {
    $this->attributes['method'] = strtoupper($method);

    return $this;
  }

  /**
   * Set the action attribute for this form.
   * @param string $action
   * @return $this
   */
  public function setAction(string $action) {
    $this->attributes['action'] = (new URL($action))->absolute();

    return $this;
  }

  /**
   * Set the enctype attribute for this form.
   * @param string $enctype
   * @return $this
   */
  public function setEnctype(string $enctype = 'multipart/form-data') {
    $this->attributes['enctype'] = $enctype;

    return $this;
  }

  /**
   * Generate the opening form tag along with hidden inputs needed for various tasks.
   * @param HTMLGenerator $html
   * @return string The generated HTML string.
   */
  public function open(HTMLGenerator $html) {
    $method = $this->attributes['method'] ?? 'POST';
    $output = $method === 'GET' ? null : (new FormInput('manix-csrf', 'hidden', CSRF_TOKEN))->toHTML($html);

    if (in_array($method, ['GET', 'POST']) === false) {
      $output .= $html->input('manix-method', 'hidden', $this->attributes['method']);
      $method = 'POST';
    }

    $this->attributes['method'] = $method;
    
    return $html->formOpen($this->attributes) . $output;
  }

  /**
   * Returns a closing form tag. This function is simply for semantic view code.
   * @return string
   */
  public function close() {
    return '</form>';
  }

  /**
   * Populate the values of input elements from a source $data.
   * @param mixed $data Must be traversable where the key is the name of the input element.
   * @return $this
   */
  public function fill($data) {
    foreach ($data as $key => $value) {
      if ($this->inputs[$key] ?? null) {
        switch ($this->inputs[$key]->getAttribute('type')) {
          case 'select':
            $this->inputs[$key]->setAttribute('selected', $value);
            break;

          case 'file': // file inputs cant have a value
          case 'password': // passwords should not be filled
            break;

          default:
            $this->inputs[$key]->setAttribute('value', $value);
        }
      }
    }

    return $this;
  }

  /**
   * Extract rules from a validation Ruleset and apply HTML5 attributes to the elements based on it.
   * @param Ruleset $r
   * @return $this
   */
  public function html5up(Ruleset $r) {
    foreach ($r->getAll() as $name => $record) {
      $input = $this->inputs[$name] ?? null;

      if ($input) {
        // add html5 attributes
        if ($record->get('required')) {
          $input->setAttribute('required', 'required');
        }
        if (($between = $record->get('between')) || ($between = $record->get('betweenX'))) {
          $input->setAttribute('min', $between[0]);
          $input->setAttribute('max', $between[1]);
          $input->setAttribute('step', round(($between[1] - $between[0]) / 10));
        }
        if ($record->has('length')) {
          $len = $record->get('length');
          $input->setAttribute('minlength', $len[0]);
          $input->setAttribute('maxlength', $len[1]);
        }

        // add data-rules attribute for javascript validation
//                $input->setAttribute('data-rules', json_encode([
//                    $record->getRules(),
//                    $record->getMessages()
//                ]));
      }
    }

    return $this;
  }

}
