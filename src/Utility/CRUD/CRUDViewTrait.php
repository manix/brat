<?php

namespace Manix\Brat\Utility\CRUD;

use Manix\Brat\Components\Forms\Form;
use Manix\Brat\Components\Views\View;
use Manix\Brat\Helpers\FormViews\DefaultFormView;
use Manix\Brat\Helpers\HTMLGenerator;
use Manix\Brat\Utility\CRUD\JavaScript\OpenSelector;
use function route;

trait CRUDViewTrait {

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
      <a href="<?= $this->getBackURL() ?>" class="btn btn-light btn-block text-left rounded-0">
        <i class="fa fa-chevron-left"></i>
      </a>
      <div class="container mt-3 mb-2">
        <div class="card">
          <div class="card-body">
            <?= $this->form() ?>
          </div>
        </div>
      </div>
      <?php
      echo $this->getSelectorCode();
    } else {
      /**
       * Kept for backwards compatibility
       */
      $class = $this->data[2]->getListView();
      echo new $class($this->data, $this->html);
    }
  }

  /**
   * @deprecated Kept for backwards compatibility
   */
  protected function getCrudListView() {
    return CRUDListView::class;
  }

  protected function getSelectorCode() {
    return new OpenSelector(null, $this->html);
  }

  protected function getBackURL() {
    return route(get_class($this->data['ctrl']));
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
    $rel = $this->data['ctrl']->getParsedRelations(true);
    $labels = $this->data['ctrl']->getLabels();

    foreach ($form->inputs() as $name => $input) {
      if ($input->type === 'submit') {
        continue;
      }

      $view->labels[$name] = $labels[$name] ?? ucfirst(str_replace('_', ' ', $name));

      if (isset($rel[$name])) {
//        $view->labels[$name] = '<i class="fa fa-link"></i> ' . $view->labels[$name];

        $input->readonly = 'readonly';
        $input->class = 'btn btn-light form-control text-left';
        $input->{'data-url'} = $this->getRelationURL($rel, $input);
        $input->onclick = 'openForeignSelector(this)';
//        $view->setCustomRenderer($input->name, [$view, 'renderForeignSelector']);
      }
    }

    $view->setCustomRenderer('manix-wipe', [$this, 'renderDelete']);

    return $view;
  }

  public function getRelationURL($relations, $input) {
    return $relations[$input->name] . $input->value;
  }
  
  public function form() {
    echo $this->constructFormView($this->data['form']);
  }

  public function renderDelete($input) {
    ?>
    <div class="card">
      <div class="text-center card-body">
        <div class="h4 p-3"><?= $this->t8('common', 'continueConfirm') ?></div>
        <?= $input->setAttribute('class', 'btn btn-danger')->toHTML($this->html) ?>
      </div>
    </div>
    <?php
  }

}
