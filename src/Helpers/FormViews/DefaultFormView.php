<?php

namespace Manix\Brat\Helpers\FormViews;

use Manix\Brat\Components\Forms\Form;
use Manix\Brat\Components\Forms\FormInput;
use Manix\Brat\Helpers\HTMLGenerator;

class DefaultFormView extends FormView {

  public $labels = [];

  public function __construct(Form $data, HTMLGenerator $html, array $labels = []) {
    parent::__construct($data, $html);
    $this->labels = array_merge($this->labels, $labels);
  }

  public function renderInput(FormInput $input) {
    echo $this->renderFormGroup($input);
  }

  public function renderFormGroup(FormInput $input) {
    switch ($input->getAttribute('type')) {
      case 'hidden':
        echo $input->toHTML($this->html);
        return;

      case 'submit':
      case 'button':
        $class = 'd-flex justify-content-end';
        $input->setAttribute('class', $input->getAttribute('class') ?? 'btn btn-secondary');
        break;
      
      
      case 'password':
        $input->setAttribute('value', '');
        // break intentionally ommited, must add form-control class
        
      default:
        $input->setAttribute('class', $input->getAttribute('class') ?? 'form-control');
        break;
    }

    $name = $input->name;
    $error = $this->data->errors[$name] ?? null;
    ?>

    <div class="form-group <?= $class ?? null, $error ? 'has-danger' : null ?>">
      <?php if ($name): ?>
        <?php if ($this->labels[$name] ?? null): ?>
          <label class="form-control-label"><?= $this->labels[$name] ?></label>
        <?php endif; ?>

        <?php if ($error): ?>
          <span class="text-danger pull-right">
            <i class="fa fa-exclamation-circle"></i>
            <?= $error ?>
          </span>
        <?php endif; ?>
      <?php endif; ?>

      <?php $this->renderInputGroup($input) ?>
    </div>

    <?php
  }

  public function renderInputGroup(FormInput $input) {
    echo $input->toHTML($this->html);
  }

}
