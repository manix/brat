<?php

namespace Manix\Brat\Utility\CRUD;

use Manix\Brat\Utility\HTTP\HTTPController;

abstract class CRUDListController extends HTTPController {

  use CRUDListExtractor;
  
  public $page = CRUDListView::class;
  
  public final function get() {
    return $this->getListData();
  }

}
