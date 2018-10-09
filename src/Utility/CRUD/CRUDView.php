<?php

namespace Manix\Brat\Utility\CRUD;

use Manix\Brat\Components\Forms\Form;
use Manix\Brat\Components\Views\View;
use Manix\Brat\Helpers\FormViews\DefaultFormView;
use Manix\Brat\Helpers\HTMLGenerator;
use Manix\Brat\Utility\CRUD\JavaScript\OpenSelector;
use Project\Views\Layouts\DefaultLayout;
use function route;

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
      echo new OpenSelector(null, $this->html);
    } else {
      /**
       * Kept for backwards compatibility
       */
      $class = $this->getCrudListView();
      echo new $class($this->data, $this->html);
    }
  }

  /**
   * @deprecated Kept for backwards compatibility
   */
  protected function getCrudListView() {
    return CRUDListView::class;
  }

  protected function constructFormView(Form $form): View {
    $view = new class($form, $this->html) extends DefaultFormView {

      public function renderForeignSelector($input) {
        ?>
        <div class="bg-light border form-group d-flex align-items-center">
          <?php if ($this->labels[$input->name] ?? null): ?>
            <label class="form-control-label ml-2 mb-0 text-nowrap"><?= $this->labels[$input->name] ?></label>
          <?php endif; ?>

          <?php $this->renderInputGroup($input) ?>
        </div>
        <?php
      }
    };
    $rel = $this->data['ctrl']->getParsedRelations();


    foreach ($form->inputs() as $name => $input) {
      if ($input->type === 'submit') {
        continue;
      }

      $view->labels[$name] = ucfirst(str_replace('_', ' ', $name));

      if (isset($rel[$input->name])) {
        $input->readonly = 'readonly';
        $input->class = 'btn btn-light form-control text-left ml-2';
        $input->{'data-url'} = $rel[$input->name];
        $input->onclick = 'openForeignSelector(this)';
        $view->setCustomRenderer($input->name, [$view, 'renderForeignSelector']);
      }
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
