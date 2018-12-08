<?php

namespace Manix\Brat\Utility\Users\Views\Settings;

use Manix\Brat\Helpers\FormViews\DefaultFormView;
use Project\Views\Users\DefaultSettingsLayout;

class DefaultSettingsView extends DefaultSettingsLayout {

  public function card() {
    ?>
    <div class="card-body">
      <?=
      new DefaultFormView($this->data['form'], $this->html, [
          'name' => $this->t8('name'),
          'new' => $this->t8('newPass'),
          'new_rpt' => $this->t8('newPassRpt'),
          'currentPassword' => $this->t8('currPass')
      ])
      ?>
    </div>
    <?php
  }

}
