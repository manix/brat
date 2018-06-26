<?php

namespace Manix\Brat\Utility\CRUD;

use Manix\Brat\Components\Collection;
use Manix\Brat\Components\Controller;
use Manix\Brat\Components\Forms\Form;
use Manix\Brat\Components\Forms\FormInput;
use Manix\Brat\Components\Model;
use Manix\Brat\Components\Views\HTML\HTMLElement;
use Manix\Brat\Helpers\FormViews\FormView;
use Manix\Brat\Helpers\HTMLGenerator;
use function route;

class CRUDListView extends HTMLElement {

  protected $fields;
  protected $controller;
  protected $controllerInstance;
  protected $pk;
  protected $sort;
  protected $order;
  protected $query;
  protected $sortable;
  public $search = true;

  /**
   * Turns a Collection into an HTML table.
   * @param HTMLGenerator $html
   * @param Collection $data
   * @param array $fields List of fields that should be displayed as columns.
   * @param Controller $controller a CRUD Controller that can manipulate the models in the collection.
   * @param array $pk The primary key definition for the models in $data.
   * @param string $sort The field that was used to sort $data
   * @param string $order The order in which $data was sorted
   * @param string $query The query that was used in order to filter $data
   * @param mixed $sortable List of sortable column names or empty.
   * @param mixed $searchable List of searchable column names or empty.
   */
  function __construct(HTMLGenerator $html, Collection $data, array $fields = [], Controller $controller = null, array $pk = [], $sort = null, $order = null, $query = null, $sortable = null, $searchable = null) {
    $this->fields = $fields;
    $this->controllerInstance = $controller;
    $this->controller = get_class($controller);
    $this->pk = empty($pk) ? $this->fields : $pk;
    $this->sort = $sort;
    $this->order = strtolower($order);
    $this->query = $query;
    $this->sortable = $sortable;
    $this->search = !empty($searchable);

    parent::__construct($data, $html);
  }

  public function html() {
    $actions = $this->controller !== null;
    $sortable = $this->controller === null ? [] : array_flip($this->getSortableFields());
    ?>
    <div class="table-responsive">
      <table class="<?= $this->getTableClass() ?>">
        <thead>
          <?php
          if ($this->controller !== null && $this->search !== false):
            $form = new Form();
            $form->setMethod('GET')->setAction(route($this->controller));
            if ($this->sort) {
              $form->add('sort', 'hidden', $this->sort);
            }
            if ($this->order) {
              $form->add('order', 'hidden', $this->order);
            }
            $form->add('query', 'text', $this->query);
            ?>
            <tr>
              <td colspan="<?= count($this->fields) + (int)$actions ?>" class="p-0">
                <div class="d-flex">
                  <?= $this->renderSearchForm($form) ?>
                  <a href="<?= route($this->controller, [$this->controllerInstance->createKey => 'yes']) ?>" class="btn btn-success rounded-0">
                    <i class="fa fa-plus"></i>
                  </a>
                </div>
              </td>
            </tr>
          <?php endif; ?>
          <tr>
            <?php foreach ($this->fields as $field): ?>
              <th>
                <?php if (isset($sortable[$field])): $asc = $this->sort === $field && $this->order === 'asc'; ?>
                  <a href="<?=
                  route($this->controller, [
                      'query' => $this->query,
                      'sort' => $field,
                      'order' => $asc ? 'desc' : 'asc'
                  ])
                  ?>" class="d-flex justify-content-between align-items-center">
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
                Actions
              </th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($this->data as $model): ?>
            <tr>
              <?php foreach ($this->fields as $field): ?>
                <td>
                  <?= $this->renderColumnBody($model, $field) ?>
                </td>
              <?php endforeach; ?>
              <?php if ($actions): ?>
                <td class="actions"><?= $this->renderActions($model) ?></td>
              <?php endif; ?>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php
  }

  /**
   * Render the contents of each table cell.
   * @param \Manix\Brat\Utility\CRUD\Model $model
   * @param string $field
   * @return string The generated HTML
   */
  public function renderColumnBody(Model $model, $field) {
    return $model->$field;
  }

  /**
   * Render the contents of the actions columns.
   * @param \Manix\Brat\Utility\CRUD\Model $model
   */
  public function renderActions(Model $model) {
    $pk = $this->extractPKValues($model);
    ?>
    <div class="btn-group">
      <a href="<?= route($this->controller, $pk) ?>" class="btn btn-light">
        <i class="fa fa-pencil"></i>
      </a>
      <a href="<?= route($this->controller, $pk + [$this->controllerInstance->deleteKey => true]) ?>" class="btn btn-warning">
        <i class="fa fa-trash"></i>
      </a>
    </div>
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
    return 'table table-bordered collection-table';
  }

  /**
   * Generate a label for each column.
   * @param string $field
   * @return string The label that will appear in the table header.
   */
  public function renderColumnLabel($field) {
    return ucfirst(str_replace('_', ' ', $field));
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

}
