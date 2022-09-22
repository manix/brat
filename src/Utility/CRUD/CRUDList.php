<?php

namespace Manix\Brat\Utility\CRUD;

use Manix\Brat\Components\Collection;
use Manix\Brat\Components\Forms\Form;
use Manix\Brat\Components\Forms\FormInput;
use Manix\Brat\Components\Model;
use Manix\Brat\Helpers\FormViews\FormView;
use Manix\Brat\Helpers\HTMLGenerator;
use Manix\Brat\Utility\CRUD\JavaScript\SelectValueForOpener;
use function route;

trait CRUDList {

  protected $fields;
  protected $controller;
  protected $controllerInstance;
  protected $pk;
  protected $sort;
  protected $order;
  protected $query;
  protected $sortable;
  protected $relations = [
  // 'field' => 'url?id='
  ];
  public $search = true;
  public $actions = true;
  public $tableHead = true;

  function __construct($data, HTMLGenerator $html) {
    list($data, $this->fields, $this->controllerInstance, $this->pk, $this->sort, $this->order, $this->query, $this->sortable, $this->search, $this->relations) = $data;

    $this->controller = get_class($this->controllerInstance);
    if (empty($this->pk)) {
      $this->pk = $this->fields;
    }
    $this->order = strtolower($this->order);
    $this->search = !empty($this->search);
    $this->labels = $this->controllerInstance->getLabels();

    parent::__construct($data, $html);
  }

  public function body() {
    $actions = $this->actions && $this->controller !== null && $this->controllerInstance->listActions();
    $sortable = $this->controller === null ? [] : array_flip($this->getSortableFields());
    $noQuery = !$this->query && $this->controllerInstance->requireQuery();
    $noResults = !$noQuery && !($this->data instanceof Collection ? $this->data->count() : count($this->data));

    if ($this->controller !== null && $this->search !== false) {
      $form = $this->getSearchForm();
    }
    ?>
    <div class="d-flex align-items-center justify-content-between bg-white">
      <?php $this->renderPageName() ?>
      <?php isset($form) ? $this->renderSearchForm($form) : '' ?>
      <?php $this->renderCreateButton() ?>
    </div>
    <?php if ($noResults): ?>
      <div class="text-center py-3 border-top">
        <?= $this->t8('common', 'noResults') ?>
      </div>
    <?php endif; ?>
    <div class="table-responsive">
      <table class="<?= $this->getTableClass() ?> table-crud m-0">
        <?php if ($this->tableHead): ?>
          <thead>
            <?php if (!$noQuery && !$noResults): ?>
              <tr>
                <?php foreach ($this->fields as $field): ?>
                  <th>
                    <?php if (isset($sortable[$field])): $asc = $this->sort === $field && $this->order === 'asc'; ?>
                      <a href="<?= $this->getSortURL($field, $asc) ?>" class="d-flex justify-content-between align-items-center">
                        <span><?= $this->renderColumnLabel($field) ?></span>
                        <?php
                        if ($this->sort === $field && isset($sortable[$field])):
                          if ($asc):
                            ?>
                            <i class="fa fa-chevron-up"></i>
                          <?php else: ?>
                            <i class="fa fa-chevron-down"></i>
                          <?php
                          endif;
                        endif;
                        ?>
                      </a>
                    <?php else: ?>
                      <span><?= $this->renderColumnLabel($field) ?></span>
                    <?php endif; ?>
                  </th>
                <?php endforeach; ?>
                <?php if ($actions): ?>
                  <th>
                    <?= html($this->getActionsLabel()) ?>
                  </th>
                <?php endif; ?>
              </tr>
            <?php endif; ?>
          </thead>
        <?php endif; ?>

        <?php if (!$noResults): ?>
          <tbody>
            <?php foreach ($this->data as $model): ?>
              <tr class="<?= $this->getRowClass($model) ?>" data-pk="<?= html($this->getRelationKey($model)) ?>">
                <?php foreach ($this->fields as $field): ?>
                  <td class="<?= $this->getColClass($model, $field) ?>">
                    <?= $this->renderColumnBody($model, $field) ?>
                  </td>
                <?php endforeach; ?>
                <?php if ($actions): ?>
                  <td class="<?= $this->getActionsClass($model) ?>"><?= $this->renderActions($model) ?></td>
                <?php endif; ?>
              </tr>
            <?php endforeach; ?>
          </tbody>
        <?php endif; ?>
      </table>
    </div>

    <?php if ($noQuery): ?>
      <div class="text-center py-3">
        <i class="fa fa-3x fa-search"></i>
        <div class="mt-2 text-muted"><?= $this->t8('common', 'enterQuery') ?></div>
      </div>
    <?php endif; ?>

    <?php
    // TODO possibly relocate script
    echo new SelectValueForOpener(NULL, $this->html);
  }

  public function renderPageName() {
    
  }

  public function getRelationKey($model) {
    return $model->{$this->pk[0]};
  }

  public function getSearchForm() {
    $form = new Form();
    $form->setMethod('GET')->setAction(route($this->controller));
    if ($this->sort) {
      $form->add('sort', 'hidden', $this->sort);
    }
    if ($this->order) {
      $form->add('order', 'hidden', $this->order);
    }
    $form->add('query', 'text', $this->query);
    return $form;
  }

  public function getRowClass(Model $model) {
    return '';
  }

  public function getColClass(Model $model, $field) {
    return '';
  }

  public function getActionsClass(Model $model) {
    return 'actions';
  }

  public function getSortURL($field, $asc) {
    return route($this->controller, [
        'query' => $this->query,
        'sort' => $field,
        'order' => $asc ? 'desc' : 'asc'
    ]);
  }

  /**
   * Render the contents of each table cell.
   * @param \Manix\Brat\Utility\CRUD\Model $model
   * @param string $field
   * @return string The generated HTML
   */
  public function renderColumnBody(Model $model, $field) {
    return isset($this->relations[$field]) ? $this->renderRelationAnchor($model, $field) : $model->$field;
  }

  public function renderRelationAnchor(Model $model, $field) {
    return $this->html->a($this->relations[$field] . $model->$field, $this->renderRelationAnchorText($model, $field));
  }

  public function renderRelationAnchorText(Model $model, $field) {
    return '#' . $model->$field;
  }

  public function getRelationHref($relation, $query) {
    return $this->relations[$relation] . $query;
  }

  public function getActionsLabel() {
      return 'Actions';
  }
  
  /**
   * Render the contents of the actions columns.
   * @param \Manix\Brat\Utility\CRUD\Model $model
   */
  public function renderActions(Model $model) {
    ?>
    <div class="btn-group">
      <?= $this->renderActionButtons($model) ?>
    </div>
    <?php
  }

  public function renderActionButtons(Model $model) {
    $pk = $this->extractPKValues($model);
    $this->renderActionButtonEdit($pk);
    $this->renderActionButtonDelete($pk);
  }

  public function renderActionButtonEdit($pk) {
    ?>
    <a href="<?= route($this->controller, $pk) ?>" class="btn btn-sm btn-light">
      <i class="fa fa-pencil"></i>
    </a>
    <?php
  }

  public function renderActionButtonDelete($pk) {
    ?>
    <a href="<?= route($this->controller, $pk + [$this->controllerInstance->deleteKey => true]) ?>" class="btn btn-sm btn-danger">
      <i class="fa fa-trash"></i>
    </a>
    <?php
  }

  public function extractPKValues(Model $model) {
    $pk = [];

    foreach ($this->pk as $field) {
      $pk[$field] = (string)$model->$field;
    }

    return $pk;
  }

  /**
   * Define the class name of the table tag.
   * @return string
   */
  public function getTableClass() {
    return 'table table-bordered collection-table bg-white';
  }

  /**
   * Generate a label for each column.
   * @param string $field
   * @return string The label that will appear in the table header.
   */
  public function renderColumnLabel($field) {
    return $this->labels[$field] ?? ucfirst(str_replace('_', ' ', $field));
  }

  /**
   * Defines the columns that should allow sorting.
   * @return array List of field names.
   */
  public function getSortableFields() {
    // By default all columns are sortable
    return $this->sortable === null ? $this->fields : $this->sortable;
  }

  public function renderSearchForm(Form $form) {
    $form->setAttribute('style', 'flex: 1');

    echo new class($form, $this->html) extends FormView {

      public function renderInput(FormInput $input) {
        if ($input->name === 'query') {
          ?>
          <div class="input-group">
            <?= $input->setAttribute('class', 'form-control rounded-0 border-0')->toHTML($this->html) ?>
            <div class="input-group-btn">
              <button type="submit" class="btn btn-light rounded-0 border-0">
                <i class="fa fa-search"></i>
              </button>
            </div>
          </div>
          <?php
        } else {
          echo $input->toHTML($this->html);
        }
      }
    };
  }

  public function getCreateButtonURL() {
    return route($this->controller, [$this->controllerInstance->createKey => 'yes']);
  }

  public function renderCreateButton() {
    ?>
    <a href="<?= $this->getCreateButtonURL() ?>" class="btn btn-success rounded-0">
      <i class="fa fa-plus"></i>
    </a>
    <?php
  }

}
