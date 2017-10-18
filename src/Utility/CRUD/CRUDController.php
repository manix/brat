<?php

namespace Manix\Brat\Utility\CRUD;

use Manix\Brat\Components\Forms\Form;
use Manix\Brat\Components\Validation\Ruleset;
use Manix\Brat\Helpers\FormController;
use Manix\Brat\Helpers\HTMLGenerator;
use PHPMailer\PHPMailer\Exception;
use Project\Views\Layouts\DefaultLayout;
use function route;

abstract class CRUDController extends FormController {

  use CRUDFoundation;
  
  /**
   * The key used to recognise a delete GET request
   */
  const DELETE = 'delete';

  protected $model;
  protected $crud_get_type;
  public $page = CRUDView::class;

  public function after($data) {
    $data['model'] = $this->getModel();
    return parent::after($data);
  }

  /**
   * Get the model that the controller is supposedly going to manipulate.
   */
  public final function getModel() {
    if ($this->model === null) {
      $pk = [];

      foreach ($this->getGateway()->getPK() as $key) {
        $pk[] = $_GET[$key] ?? null;
      }

      $this->model = $this->fetchModel($pk);
    }

    return $this->model;
  }

  /**
   * Fetch the model from the persistence layer. This will get called only once.
   * @param array The primary key values for the model.
   * @return mixed The model or false if not found.
   */
  protected function fetchModel($pk) {
    return $this->getGateway()->find(...$pk)->first() ?? false;
  }

  /**
   * Get the crud type that the controller is operating in.
   * @return string One character string, one of the following: c, d, u
   */
  public final function getCRUDType() {
    if ($this->crud_get_type === null) {
      if ($this->getModel() === false) {
        $this->crud_get_type = 'c';
      } else if ($_GET[self::DELETE] ?? null) {
        $this->crud_get_type = 'd';
      } else {
        $this->crud_get_type = 'u';
      }
    }

    return $this->crud_get_type;
  }

  public final function get() {

    if ($this->page === null) {
      switch ($this->getCRUDType()) {
        case 'c': $this->page = $this->getCreateView();
          break;
        case 'u': $this->page = $this->getUpdateView();
          break;
        case 'd': $this->page = $this->getDeleteView();
          break;
        default: throw new Exception('not found', 404);
      }
    }

    return [
        'form' => $this->getForm(),
    ];
  }

  public function put() {
    return $this->validate($_REQUEST, function($data) {
      $model = $this->getModel();
      
      foreach ($data as $key => $value) {
        $model->$key = $value;
      }

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

      if (isset($wipeOld)) {
        $this->getGateway()->wipe(...$pk);
      }

      return [
          'success' => true,
          'goto' => route(static::class, $pk_route)
      ];
    });
  }

  public function post() {
    return $this->validate($_REQUEST, function($data) {
      $gate = $this->getGateway();

      $class = $gate::MODEL;
      $this->model = new $class($data);

      // just in case
      unset($data[$gate->getAI()]);

      if (!$gate->persist($this->model)) {
        throw new Exception('Unexpected error.', 500);
      }

      $pk = [];

      foreach ($gate->getPK() as $key) {
        $pk[$key] = (string)$this->model->$key;
      }
      
      return [
          'success' => true,
          'model' => $this->model,
          'goto' => route(static::class, $pk)
      ];
    });
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

  /**
   * Get the view that must render the update GET response.
   * @return string FQCN
   */
  public function getUpdateView() {
    return CRUDView::class;
  }

  /**
   * Get the view that must render the create GET response.
   * @return string FQCN
   */
  public function getCreateView() {
    return CRUDView::class;
  }

  /**
   * Get the view that must render the delete GET response.
   * @return string FQCN
   */
  public function getDeleteView() {
    return CRUDView::class;
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
      // Skip adding inputs for primary key properties
      if ($key === $ai) {
        continue;
      }

      $form->add($key, 'text');
    }

    $form->add('manix-create', 'submit', $this->t8('common', 'create'));

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
    $form->setMethod('PUT');

    foreach ($this->getEditableFields() as $key) {
      $form->add($key, 'text');
    }

    $form->add('manix-save', 'submit', $this->t8('common', 'save'));

    return $form->fill($this->getModel());
  }

  /**
   * Get the rule set that is going to validate data for updating a model.
   * @param Ruleset $rules A blank rule set.
   * @return Ruleset The constructed rule set.
   */
  protected function constructUpdateRules(Ruleset $rules) {
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

}

class CRUDView extends DefaultLayout {

  use CRUDViewTrait;

  public function __construct($data, HTMLGenerator $html) {

    parent::__construct($data, $html);

    if ($data['success'] ?? null) {
      header('Location: ' . $data['goto'] ?? null);
      exit;
    }
  }

  public function body() {
    $this->form();
  }

}
