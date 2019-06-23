<?php

namespace Manix\Brat\Utility\CRUD;

use Exception;
use Manix\Brat\Components\Criteria;
use Manix\Brat\Components\Forms\Form;
use Manix\Brat\Components\Model;
use Manix\Brat\Components\Persistence\Gateway;
use Manix\Brat\Components\Sorter;
use Manix\Brat\Components\Validation\Ruleset;
use Manix\Brat\Helpers\FormEndpoint;
use Manix\Brat\Helpers\Image;
use Manix\Brat\Helpers\Redirect;
use const SITE_URL;
use function route;

trait CRUDEndpoint {

  use FormEndpoint;

  // The key used to recognize a create GET request
  public $createKey = 'new';
  // The key used to recognize a delete GET request
  public $deleteKey = 'delete';
  protected $model;
  protected $operation;
  protected $gate;

  public final function getGateway(): Gateway {
    if ($this->gate === null) {
      $this->gate = $this->constructGateway();
    }

    return $this->gate;
  }

  abstract protected function constructGateway(): Gateway;

  /**
   * Define whether records should be displayed only when there is a query.
   * @return boolean
   */
  public function requireQuery() {
    return false;
  }

  /**
   * Removes empty strings from the array which are relations
   * @param type $input
   */
  public function cleanInput($input) {
    $rel = $this->getRelations();

    foreach ($input as $key => $value) {
      if (isset($rel[$key]) && $value === '') {
        unset($_REQUEST[$key]);
      }
    }
  }

  public function after($data) {

    if ($this->getCRUDType() !== 'l') {
      $data['ctrl'] = $this;
      $data['model'] = $this->getModel();
    }

    if (!empty($data['success']) && isset($data['goto'])) {
      new Redirect($data['goto']);
    }

    return parent::after($data);
  }

  /**
   * Get the model that the controller is supposedly going to manipulate.
   */
  public final function getModel() {
    if ($this->model === null) {
      $criteria = $this->getCriteria();

      $matchPK = $criteria->group('AND');

      foreach ($this->getGateway()->getPK() as $key) {
        if (isset($_GET[$key])) {
          $matchPK->equals($key, $_GET[$key]);
        } else {
          return false;
        }
      }

      $this->model = $this->fetchModel($criteria);
    }

    return $this->model;
  }

  /**
   * Fetch the model from the persistence layer. This will get called only once.
   * @param array The primary key values for the model.
   * @return mixed The model or false if not found.
   */
  protected function fetchModel(Criteria $criteria) {
    return $this->getGateway()->findBy($criteria)->first() ?? false;
  }

  /**
   * Get the crud type that the controller is operating in.
   * @return string One character string, one of the following: c, d, u
   */
  public final function getCRUDType() {
    if ($this->operation === null) {
      if ($this->getModel() === false) {
        if (isset($_GET[$this->createKey])) {
          $this->operation = 'c';
        } else {
          $this->operation = 'l';
        }
      } else if ($_GET[$this->deleteKey] ?? null) {
        $this->operation = 'd';
      } else {
        $this->operation = 'u';
      }
    }

    return $this->operation;
  }

  public function get() {

    if ($this->page === null) {
      switch ($this->getCRUDType()) {
        case 'c': $this->page = $this->getCreateView();
          break;
        case 'u': $this->page = $this->getUpdateView();
          break;
        case 'd': $this->page = $this->getDeleteView();
          break;
        case 'l':
          $this->page = $this->getListView();
          return $this->getListData();

        default:
          throw new Exception('not found', 404);
      }
    }

    return [
        'form' => $this->getForm(),
    ];
  }

  public function populateModel(Model $model, $data) {
    foreach ($this->getEditableFields() as $field) {
      if (isset($data[$field])) {
        $model->$field = $data[$field];
      }
    }
  }

  public function put() {
    $this->cleanInput($_REQUEST);

    return $this->validate($_REQUEST, function($data) {
      $model = $this->getModel();

      $this->populateModel($model, $data);

      if (!$this->getGateway()->persist($model)) {
        throw new Exception('Unexpected error.', 500);
      }

      $pk = [];
      $pk_route = [];

      foreach ($this->getGateway()->getPK() as $key) {
        $pk[] = $_GET[$key];
        $pk_route[$key] = (string)$model->$key;

        if ($model->$key != $_GET[$key]) {
          $wipeOld = true;
        }
      }

      $this->saveImages($pk_route);

      if (isset($wipeOld)) {
        $this->getGateway()->wipe(...$pk);
      }

      return [
          'success' => true,
          'goto' => $this->getPutReturnURL($pk_route)
      ];
    });
  }

  public function getPutReturnURL($pk) {
    return route(static::class, $pk);
  }

  public function post() {
    $this->cleanInput($_REQUEST);

    return $this->validate($_REQUEST, function($data) {
      $gate = $this->getGateway();

      // just in case
      $data[$gate->getAI()] = null;

      $class = $gate::MODEL;
      $this->model = new $class();
      $this->populateModel($this->model, $data);
      
      if (!$gate->persist($this->model)) {
        throw new Exception('Unexpected error.', 500);
      }

      $pk = [];

      foreach ($gate->getPK() as $key) {
        $pk[$key] = (string)$this->model->$key;
      }

      $this->saveImages($pk);

      return [
          'success' => true,
          'model' => $this->model,
          'goto' => $this->getPostReturnURL($pk)
      ];
    });
  }

  public function getPostReturnURL($pk) {
    return route(static::class, $pk);
  }

  public function getLabels() {
    return [
    // field => label  
    ];
  }

  public function delete() {
    $model = $this->getModel();
    $gate = $this->getGateway();

    if (!$model) {
      throw new Exception('Can not delete non existent model.', 500);
    }

    $pk = [];

    foreach ($gate->getPK() as $key) {
      $pk[] = $model->$key;
    }

    if (!$gate->wipe(...$pk)) {
      throw new Exception('Could not delete.', 500);
    }

    return [
        'success' => true,
        'goto' => $this->afterDelete()
    ];
  }

  public function getCRUDView() {
    return CRUDView::class;
  }

  /**
   * Get the view that must render the update GET response.
   * @return string FQCN
   */
  public function getUpdateView() {
    return $this->getCRUDView();
  }

  /**
   * Get the view that must render the create GET response.
   * @return string FQCN
   */
  public function getCreateView() {
    return $this->getCRUDView();
  }

  /**
   * Get the view that must render the delete GET response.
   * @return string FQCN
   */
  public function getDeleteView() {
    return $this->getCRUDView();
  }

  /**
   * Get the array of fields that should be presented for editing on create and update.
   * @return array List of model property names (fields).
   */
  public function getEditableFields() {
    return $this->getGateway()->getFields();
  }

  /**
   * Get the form that is going to create new models.
   * @param Form $form A blank form instance.
   * @return Form The constructed form.
   */
  protected function constructCreateForm(Form $form) {
    $form->setMethod('POST');

    $ai = $this->getGateway()->getAI();

    foreach ($this->getEditableFields() as $key) {
      // Skip adding inputs for primary key properties and timestamps
      if ($key === $ai || $key === Gateway::TIMESTAMP_CREATED || $key === Gateway::TIMESTAMP_UPDATED) {
        continue;
      }

      $form->add($key, 'text');
    }

    if ($this->addImageInputs($form)) {
      $form->setEnctype();
    }

    $form->add('manix-save', 'submit', $this->t8('common', 'create'));

    return $form;
  }

  /**
   * Get the rule set that is going to validate data for creating a new model.
   * @param Ruleset $rules A blank rule set.
   * @return Ruleset The constructed rule set.
   */
  protected function constructCreateRules(Ruleset $rules) {
    return $rules;
  }

  /**
   * Get the form that is going to update models.
   * @param Form $form A blank form instance.
   * @return Form The constructed form.
   */
  protected function constructUpdateForm(Form $form) {
    $form = $this->constructCreateForm($form);

    $form->setMethod('PUT');
    $form->input('manix-save')->value = $this->t8('common', 'save');

    return $form->fill($this->getModel());
  }

  /**
   * Get the rule set that is going to validate data for updating a model.
   * @param Ruleset $rules A blank rule set.
   * @return Ruleset The constructed rule set.
   */
  protected function constructUpdateRules(Ruleset $rules) {
    $rules = $this->constructCreateRules($rules);

    foreach ($this->getGateway()->getPK() as $key) {
      $rules->add($key)->required();
    }

    return $rules;
  }

  /**
   * Construct the form that will wipe the model.
   * @param Form $form A blank form instance.
   * @return Form
   */
  protected function constructDeleteForm(Form $form) {
    $form->setMethod('DELETE');
    $form->add('manix-wipe', 'submit', $this->t8('common', 'delete'));

    return $form;
  }

  /**
   * Get the rule set that is going to validate data for deleting a model.
   * @param Ruleset $rules A blank rule set.
   * @return Ruleset The constructed rule set.
   */
  protected function constructDeleteRules(Ruleset $rules) {
    return $rules;
  }

  /**
   * Get the URL at which the user should be redirected after successful delete.
   */
  protected function afterDelete() {
    return route(static::class);
  }

  protected final function constructForm(Form $form): Form {
    switch ($this->getCRUDType()) {
      case 'c': return $this->constructCreateForm($form);
      case 'u': return $this->constructUpdateForm($form);
      case 'd': return $this->constructDeleteForm($form);
      default: return $form;
    }
  }

  protected final function constructRules(Ruleset $rules): Ruleset {
    switch ($this->getCRUDType()) {
      case 'c': return $this->constructCreateRules($rules);
      case 'u': return $this->constructUpdateRules($rules);
      case 'd': return $this->constructDeleteRules($rules);
      default: return $rules;
    }
  }

  /**
   * LIST EXTRACTOR
   */
  public function getSorter(): Sorter {
    $sort = $this->getSort();

    return new Sorter(in_array($sort, $this->getSortableColumns()) ? $sort : null, $this->getOrder() ?? 'asc');
  }

  public function getColumns() {
    return $this->fetchColumns();
  }

  public function fetchColumns() {
    return $this->getGateway()->getFields();
  }

  public function getSortableColumns() {
    return $this->getColumns();
  }

  public function getSearchableColumns() {
    return $this->getColumns();
  }

  protected function getSort() {
    return $_GET['sort'] ?? null;
  }

  protected function getOrder() {
    return $_GET['order'] ?? null;
  }

  protected function getQuery() {
    return $_GET['query'] ?? null;
  }

  protected function getQueryFields($searchable) {
    $input = $_GET['fields'] ?? null;
    $fields = [];

    if (!$input || !is_array($input)) {
      foreach ($searchable as $field) {
        $fields[$field] = $this->getDefaultSearchComparator();
      }
    } else {
      foreach ($input as $field => $comparator) {
        if (!$comparator) {
          $comparator = $this->getDefaultSearchComparator();
        }

        if (in_array($field, $searchable)) {
          $fields[$field] = $comparator;
        }
      }
    }

    return $fields;
  }

  protected function getDefaultSearchComparator() {
    return 'like';
  }

  protected function getCriteria() {
    return new Criteria;
  }

  /**
   * The view that will be used to render the list page.
   */
  public function getListView() {
    // CRUDView automatically calls CRUDListView internally.
    return CRUDListView::class;
  }

  /**
   * Whether to display actions in listview or not
   * @return boolean
   */
  public function listActions() {
    return true;
  }

  /**
   * Defines the relations to other CRUD controllers to enable hyperlinks in column bodies
   * @return array Keys are key names in this controller's gateway relations and values are FQCNs of respective CRUD controllers
   */
  public function getRelations() {
    return [
//    'relation-name-in-gateway' => class | [field, class, search_fields]
    ];
  }

  public function getParsedRelations($alwaysList = false) {
    $relations = [];
    $gateRelations = $this->getGateway()->getRelations();
    foreach ($this->getRelations() as $key => $data) {
      $rel = $gateRelations[$key];
      if (is_array($data)) {
        $field = $data[0];
        $class = $data[1];
      } else {
        $field = $rel[1] ?? $key;
        $class = $data;
      }

      $gate = new $rel[0];
      $pk = $gate->getPK();

      if (is_array($field)) {
        $values = [];
        $fields = [];
        $model = $this->getModel();
        foreach ($field as $f) {
          $values[$f] = $model->$f ?? $_REQUEST[$f] ?? null;
          if ($values[$f]) {
            $fields[$f] = 'equals';
          }
        }
        $relations[$key] = route($class, $alwaysList || count($pk) > 1 ? [
            'fields' => is_array($data) ? $data[2] : $fields,
            'query' => $values
        ] : [
            $pk[0] => ''
        ]);
      } else {
        $relations[$field] = route($class, $alwaysList || count($pk) > 1 ? [
            'fields' => $data[2] ?? $pk[0],
            'query' => ''
        ] : [
            $pk[0] => ''
        ]);
      }
    }

    return $relations;
  }

  public function getListData() {
    $definedColumns = $this->fetchColumns();

    if (isset($_GET['columns'])) {
      $columns = [];
      foreach (is_array($_GET['columns']) ? $_GET['columns'] : explode(',', $_GET['columns']) as $col) {
        if (in_array($col, $definedColumns)) {
          $columns[] = $col;
        }
      }
    } else {
      $columns = $definedColumns;
    }

    $searchable = $this->getSearchableColumns();
    $query = $this->getQuery();

    if (empty($searchable) && $this->requireQuery()) {
      throw new Exception('Controller with mandatory query requires at least 1 searchable column', 500);
    }

    $criteria = $this->getCriteria();

    if ($query) {
      $queryCriteria = $criteria->group($_GET['glue'] ?? 'OR');

      foreach ($this->getQueryFields($searchable) as $field => $comparator) {
        $queryCriteria->$comparator($field, $query[$field] ?? $query);
      }
    }

    $gate = $this->getGateway();
    $gatedefaultcols = $gate->getFields();
    $gatecols = [];
    foreach ($columns as $col) {
      if (in_array($col, $gatedefaultcols)) {
        $gatecols[] = $col;
      }
    }
    $gate->setFields($gatecols);

    return [
        $query || !$this->requireQuery() ? $gate->sort($this->getSorter())->findBy($criteria) : [],
        $this->getColumns(),
        $this,
        $gate->getPK(),
        $this->getSort(),
        $this->getOrder(),
        $query,
        $this->getSortableColumns(),
        $searchable,
        $this->getParsedRelations()
    ];
  }

  public function saveImages($pk) {
    foreach ($this->getImageNamespaces() as $namespace) {
      $file = $_FILES[$namespace] ?? 0;

      if ($file && $file['error'] === UPLOAD_ERR_OK) {
        $data = $this->getImageData($namespace);
        $path = $data['base'] . $data['path'] . '/' . implode('/', $pk);

        $i = Image::fromFile($file['tmp_name']);
        $i->setType($data['type'] ?? IMAGETYPE_JPEG);
        $i->setFile($path, true);
        if ($data['resize']) {
          $i->resize(...$data['resize']);
        }
        $i->save(...($data['options'] ?? []));

        if ($data['thumb']) {
          $c = clone $i;
          $c->setFile($path . '/thumbs/', true);
          $c->resize(128, 128);
        }
      }
    }
  }

  public function addImageInputs(Form $form) {
    $pk = [];

    foreach ($this->getGateway()->getPK() as $field) {
      $pk[] = $_REQUEST[$field] ?? $form->input($field)->value ?? '';
    }

    $pk = implode('/', $pk);

    foreach ($this->getImageNamespaces() as $ns) {
      $data = $this->getImageData($ns);
      $path = SITE_URL . $data['path'] . '/' . $pk . image_type_to_extension($data['type'] ?? IMAGETYPE_JPEG);

      $form->add($ns)
      ->setAttribute('type', 'file')
      ->setAttribute('style', 'background: no-repeat center url(' . $path . '); background-size: cover; min-height: 256px');
    }

    return isset($ns);
  }

  public function getImageNamespaces() {
    return [];
  }

  public function getImageData($namespace) {
    return [
        'base' => PUBLIC_PATH,
        'path' => '/images/crud/' . $namespace,
        'type' => IMAGETYPE_JPEG,
        'thumb' => false,
        'resize' => [1024, 768],
        'options' => [90]
    ];
  }

}
