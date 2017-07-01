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

    <div class="card-block">
      <div class="d-flex justify-content-center">
        <img style="max-height: 500px" class="img-fluid" src="<?= Auth::user()->getPhotoURL() ?>" alt="Card image cap">
      </div>
    </div>

    <div class="card-header">
      <?= $this->t8('uploadNewPhoto') ?>
    </div>
    <div class="card-block">
      <div class="alert alert-info">
        <?= $this->t8('photoTip') ?>
      </div>
      <?= new DefaultFormView($this->data['form'], $this->html, []) ?>
    </div>


    <?php
  }

}

class AddEmailFormView extends DefaultFormView {

  public function renderInputGroup(FormInput $input) {
    ?>
    <div class="input-group">
      <?= $input->toHTML($this->html) ?>
      <div class="input-group-btn">
        <button type="submit" class="btn btn-success">
          <i class="fa fa-plus"></i>
        </button>
      </div>
    </div>
    <?php
  }

}

class EmailListFormView extends DefaultFormView {

  public function renderInputGroup(FormInput $input) {
    ?>
    <div class="input-group">
      <span class="form-control-static">
        <?= html($input->value) ?>
      </span>
      <div class="input-group-btn">
        <button type="submit" class="btn btn-link text-danger">
          <i class="fa fa-trash"></i>
        </button>
      </div>
    </div>
    <?php
  }

}
