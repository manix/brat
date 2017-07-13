<?php

namespace Manix\Brat\Utility\CRUD;

use Manix\Brat\Components\Controller;

abstract class CRUDListController extends Controller {

  use CRUDListExtractor;
  
  public $page = CRUDListView::class;
  
  public final function get() {
    return $this->getListData();
  }

}
