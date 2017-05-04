<?php

namespace Manix\Brat\Utility\CRUD;

use Manix\Brat\Components\Forms\Form;
use Manix\Brat\Helpers\FormViews\DefaultFormView;

trait CRUDViewTrait {

  protected function constructFormView(Form $form) {
    $view = new DefaultFormView($form, $this->html);

    foreach ($form->inputs() as $name => $input) {
      if ($input->type === 'submit') {
        continue;
      }

      $view->labels[$name] = ucfirst(str_replace('_', ' ', $name));
    }

    $view->setCustomRenderer('manix-wipe', [$this, 'renderDelete']);
    
    return $view;
  }

  public function form() {
    echo $this->constructFormView($this->data['form']);
  }

  public function renderDelete($input) {
    ?>
    <div class="text-center">
      <div class="h4 p-3"><?= $this->t8('common', 'continueConfirm') ?></div>
      <?= $input->setAttribute('class', 'btn btn-danger')->toHTML($this->html) ?>
    </div>
    <?php
  }

}
