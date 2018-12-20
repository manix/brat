<?php

namespace Manix\Brat\Helpers\FormViews;

use Manix\Brat\Components\Forms\Form;
use Manix\Brat\Components\Forms\FormInput;
use Manix\Brat\Components\Views\HTML\HTMLElement;
use Manix\Brat\Helpers\HTMLGenerator;

abstract class FormView extends HTMLElement {

  protected $order = [];
  protected $customRenderers = [];

  public function __construct(Form $data, HTMLGenerator $html) {
    parent::__construct($data, $html);
  }

  /**
   * Define a function that will be used to render a specific input element from the form.
   * @param string $name The name of the input to use this renderer upon.
   * @param callable $renderer A callable that will be invoked with the FormInput element and must echo out the appropriate result.
   */
  public function setCustomRenderer($name, callable $renderer) {
    $this->customRenderers[$name] = $renderer;
  }

  /**
   * Specify in what order inputs should be rendered.
   * @param array $order Array of input names.
   */
  public function order(array $order) {
    $this->order = $order;
  }

  abstract public function renderInput(FormInput $input);

  public function html() {

    echo $this->data->open($this->html);

    $rendered = [];

    foreach ($this->order as $name) {
      $rendered[$name] = true;

      $this->performInputRender($this->data->input($name));
    }

    foreach ($this->data->inputs() as $input) {
      if (isset($rendered[$input->name])) {
        continue;
      }

      $this->performInputRender($input);
    }

    echo $this->data->close();
  }

  protected function performInputRender(FormInput $input) {
    if (isset($this->customRenderers[$input->name])) {
      $this->customRenderers[$input->name]($input, $this);
    } else {
      $this->renderInput($input);
    }
  }

}
