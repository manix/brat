<?php

namespace Manix\Brat\Helpers\FormViews;

use Project\Views\Layouts\DefaultLayout;
use function html;

class FormPageView extends DefaultLayout {

  public function body() {
    if (!isset($this->data['status'])) {
      $view = new DefaultFormView($this->data['form'], $this->html);
      foreach ($this->data['form']->inputs() as $name => $input) {
        if ($input->type === 'submit') {
          continue;
        }

        $view->labels[$name] = ucfirst(str_replace('_', ' ', $name));
      }
      ?>
      <div class="card rounded-0">
        <div class="card-body">
          <?= $view ?>
        </div>
      </div>
      <?php
    } else {
      $level = $this->data['status'] ? 'success' : 'danger';
      $msg = $this->data['message'] ?? ($this->data['status'] ? 'Success' : 'Error');
      ?>
      <div class="alert alert-<?= $level ?>">
        <?= gettype($msg) === 'string' ? html($msg) : $msg ?>
      </div>
      <?php
    }
  }

}
