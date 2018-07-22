<?php

namespace Manix\Brat\Utility\Users\Controllers\Settings;

use Manix\Brat\Components\Forms\Form;
use Manix\Brat\Components\Validation\Ruleset;
use Manix\Brat\Utility\Users\Views\Settings\IndexView;

class Index extends SettingsController {

  public $page = IndexView::class;

  

  protected function constructForm(Form $form): Form {
    return $form;
  }

  protected function constructRules(Ruleset $rules): Ruleset {
    return $rules;
  }

}
