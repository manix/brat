<?php

namespace Manix\Brat\Components\Forms\Views;

use Manix\Brat\Components\Forms\FormInput;

class DefaultFormView extends FormView {

  public function renderInput(FormInput $input) {
    switch ($input->getAttribute('type')) {
      case 'hidden':
        echo $input->toHTML($this->html);
        return;

      case 'submit':
      case 'button':
        $class = 'd-flex justify-content-end';
        $input->setAttribute('class', $input->getAttribute('class') ?? 'btn btn-secondary');
        break;

      default:
        $input->setAttribute('class', $input->getAttribute('class') ?? 'form-control');
        break;
    }

    $error = $this->data->errors[$input->name] ?? null;
    ?>

    <div class="form-group <?= $class ?? null, $error ? 'has-danger' : null ?>">
      <?php if ($input->name): ?>
        <label><?= ucfirst(str_replace('_', ' ', $input->name)) ?></label>
        <?php if ($error): ?>
          <span class="text-danger pull-right">
            <i class="fa fa-exclamation-circle"></i>
            <?= $error ?>
          </span>
        <?php endif; ?>
      <?php endif; ?>

      <?= $input->toHTML($this->html) ?>
    </div>

    <?php
  }

}
