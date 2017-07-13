<?php

namespace Manix\Brat\Utility\CRUD;

use Manix\Brat\Components\Criteria;
use Manix\Brat\Components\Sorter;

trait CRUDListExtractor {

  protected $crudController;

  abstract protected function constructCRUDController(): CRUDController;

  public function getCRUDController() {
    if (!$this->crudController) {
      $this->crudController = $this->constructCRUDController();
    }

    return $this->crudController;
  }

  public function getSorter(): Sorter {
    $sort = $this->getSort();

    return new Sorter(in_array($sort, $this->getSortableColumns()) ? $sort : null, $this->getOrder() ?? 'asc');
  }

  public function getColumns() {
    return $this->getCRUDController()->getGateway()->getFields();
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

  public function getListData() {
    $controller = $this->getCRUDController();
    $searchable = $this->getSearchableColumns();
    $query = $this->getQuery();
    $criteria = new Criteria;
    
    if ($query) {
      $queryCriteria = $criteria->group('OR');
      
      foreach ($searchable as $field) {
        $queryCriteria->like($field, '%' . $query . '%');
      }
    }

    return [
        $controller->getGateway()->sort($this->getSorter())->findBy($criteria),
        $this->getColumns(),
        get_class($controller),
        $controller->getGateway()->getPK(),
        get_class($this),
        $this->getSort(),
        $this->getOrder(),
        $query,
        $this->getSortableColumns(),
        $searchable
    ];
  }

}
