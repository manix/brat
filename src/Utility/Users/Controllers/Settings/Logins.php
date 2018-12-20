<?php

namespace Manix\Brat\Utility\Users\Controllers\Settings;

use Manix\Brat\Components\Forms\Form;
use Manix\Brat\Components\Sorter;
use Manix\Brat\Components\Validation\Ruleset;
use Project\Traits\Users\UserGatewayFactory;
use Manix\Brat\Utility\Users\Models\Auth;
use Manix\Brat\Utility\Users\Views\Settings\LoginsView;

class Logins extends SettingsController {

  use UserGatewayFactory;

  public $page = LoginsView::class;

  public function get() {

    $gate = $this->getLoginGateway();
    $gate->sort(new Sorter($gate::TIMESTAMP_CREATED));
    $gate->limit = $this->getDisplayedLoginsCount();

    return [
        'logins' => $gate->find(Auth::id())
    ];
  }

  /**
   * Return the number of login attempts that should be displayed.
   * @return int
   */
  protected function getDisplayedLoginsCount(): int {
    return 100;
  }

  protected function constructForm(Form $form): Form {
    return $form;
  }

  protected function constructRules(Ruleset $rules): Ruleset {
    return $rules;
  }

}
