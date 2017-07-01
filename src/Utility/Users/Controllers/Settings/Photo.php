<?php

namespace Manix\Brat\Utility\Users\Controllers\Settings;

use Manix\Brat\Components\Forms\Form;
use Manix\Brat\Components\Validation\Ruleset;
use Manix\Brat\Helpers\Image;
use Manix\Brat\Utility\Users\Controllers\UserGatewayFactory;
use Manix\Brat\Utility\Users\Models\Auth;
use Manix\Brat\Utility\Users\Models\UserGateway;
use Manix\Brat\Utility\Users\Views\Settings\PhotoView;
use const PUBLIC_PATH;

class Photo extends SettingsController {

  use UserGatewayFactory;

  public $page = PhotoView::class;

  public function __construct() {
    parent::__construct();

    $this->addSaveButton();
  }

  public function post() {

    return $this->validate($_FILES, function($data) {
      // TODO manage paths better, possibly centralize

      $user = Auth::user();

      $img = Image::fromFile($data['photo']['tmp_name']);
      $img->setType(IMAGETYPE_PNG);
      $img->setFile(PUBLIC_PATH . '/assets/images/users/hd/' . $user->id);
      $img->save();

      $thumb = clone $img;
      $thumb->setFile(PUBLIC_PATH . '/assets/images/users/thumb/' . $user->id);
      $thumb->save();

      $user->photo_rev++;

      $gate = new UserGateway;
      $gate->persist($user);
      Auth::updateCache($user);

      return ['success' => true];
    });
  }

  protected function constructForm(Form $form): Form {
    $form->setEnctype();
    $form->add('photo', 'file');

    return $form;
  }

  protected function constructRules(Ruleset $rules): Ruleset {
    $rules->add('photo')->required()->file(5);

    return $rules;
  }

}
