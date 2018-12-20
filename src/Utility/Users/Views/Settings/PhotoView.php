<?php

namespace Manix\Brat\Utility\Users\Views\Settings;

use Manix\Brat\Components\Forms\FormInput;
use Manix\Brat\Helpers\FormViews\DefaultFormView;
use Manix\Brat\Utility\Users\Models\Auth;
use Project\Views\Users\DefaultSettingsLayout;
use function html;

class PhotoView extends DefaultSettingsLayout {

  public function card() {

    $this->cacheT8('manix/util/users/settings');
    ?>
    <div class="card-header">
      <?= $this->t8('currentPhoto') ?>
    </div>

    <div class="card-body">
      <div class="d-flex justify-content-center">
        <img style="max-height: 500px" class="img-fluid" src="<?= Auth::user()->getPhotoURL() ?>" alt="Card image cap">
      </div>
    </div>

    <div class="card-header">
      <?= $this->t8('uploadNewPhoto') ?>
    </div>
    <div class="card-body">
      <div class="alert alert-info">
        <?= $this->t8('photoTip') ?>
      </div>
      <?= new DefaultFormView($this->data['form'], $this->html, []) ?>
    </div>


    <?php
  }

}
