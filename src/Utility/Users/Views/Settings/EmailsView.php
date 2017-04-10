<?php

namespace Manix\Brat\Utility\Users\Views\Settings;

use Manix\Brat\Components\Forms\FormInput;
use Manix\Brat\Helpers\FormViews\DefaultFormView;

class EmailsView extends DefaultSettingsLayout {

  public function card() {

    $this->data['delete']->setAttribute('class', 'list-group');
    ?>
    <div class="card-header">
      Add address
    </div>

    <div class="card-block">
      <?= new AddEmailFormView($this->data['form'], $this->html) ?>
    </div>

    <div class="card-header">
      Your addresses
    </div>
    <div class="card-block">
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
