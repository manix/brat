<?php

namespace Manix\Brat\Utility\CRUD;

use Manix\Brat\Components\Forms\Form;
use Manix\Brat\Components\Views\View;
use Manix\Brat\Helpers\FormViews\DefaultFormView;
use Manix\Brat\Helpers\HTMLGenerator;
use Project\Views\Layouts\DefaultLayout;

class CRUDView extends DefaultLayout {

  public function __construct($data, HTMLGenerator $html) {

    parent::__construct($data, $html);

    if ($data['success'] ?? null) {
      header('Location: ' . $data['goto'] ?? null);
      exit;
    }
  }

  public function body() {
    if (isset($this->data['form'])) {
      ?>
      <div class="container mt-4 mb-2">
        <div class="text-center mb-2">
          <a href="<?= route(get_class($this->data['ctrl'])) ?>">
            <?= $this->t8('common', 'toList') ?>
          </a>
        </div>
        <?= $this->form() ?>
      </div>
      <?php
    } else {
      $class = $this->getCrudListView();
      echo new $class($this->html, ...$this->data);
    }
  }

  protected function getCrudListView() {
    return CRUDListView::class;
  }

  protected function constructFormView(Form $form): View {
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
