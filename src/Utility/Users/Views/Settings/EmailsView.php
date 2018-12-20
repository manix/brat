<?php

namespace Manix\Brat\Utility\Users\Views\Settings;

use Manix\Brat\Components\Forms\Form;
use Manix\Brat\Components\Forms\FormInput;
use Manix\Brat\Helpers\FormViews\DefaultFormView;
use Manix\Brat\Helpers\HTMLGenerator;
use Project\Views\Users\DefaultSettingsLayout;
use function html;

class EmailsView extends DefaultSettingsLayout {

  public function card() {

    $this->data['delete']->setAttribute('class', 'list-group');
    ?>
    <div class="card-header">
      Add address
    </div>

    <div class="card-body">
      <?= new AddEmailFormView($this->data['form'], $this->html) ?>
    </div>

    <div class="card-header">
      Your addresses
    </div>
    <div class="card-body">
      <?= $this->data['delete']->open($this->html) ?>

      <?php foreach ($this->data['addresses'] as $email): ?>
        <div class="list-group-item">
          <div class="d-flex w-100 justify-content-between">
            <span>
              <?= html($email->email) ?>
            </span>

            <div class="text-right">
              <?php if ($email->isVerified()): ?>
                <i class="fa fa-check text-success px-2"></i>
              <?php else: ?>
                <small class="px-2">
                  <?= $this->t8('manix/util/users/common', 'emailNotVerified') ?>
                </small>
              <?php endif; ?>

              <button name="email" type="submit" value="<?= html($email->email) ?>" class="btn btn-link p-0 <?= $email->isVerified() ? 'text-muted' : 'text-danger' ?>">
                <i class="fa fa-trash"></i>
              </button>
            </div>
          </div>
        </div>
      <?php endforeach; ?>

      <?= $this->data['delete']->close() ?>

    </div>


    <?php
  }

}

class AddEmailFormView extends DefaultFormView {

  public function __construct(Form $data, HTMLGenerator $html, array $labels = array()) {
    $labels['currentPassword'] = $this->t8('manix/util/users/settings', 'currPass');

    parent::__construct($data, $html, $labels);
  }

  public function renderInputGroup(FormInput $input) {
    if ($input->name === 'email') {
      ?>
      <div class="input-group">
        <?= $input->setAttribute('class', 'form-control')->toHTML($this->html) ?>
        <div class="input-group-btn">
          <button type="submit" class="btn btn-success">
            <i class="fa fa-plus"></i>
          </button>
        </div>
      </div>
      <?php
    } else {
      parent::renderInputGroup($input);
    }
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
