<?php

namespace Manix\Brat\Utility\Users\Views;

use Manix\Brat\Helpers\FormViews\DefaultFormView;
use Manix\Brat\Components\Views\HTML\HTMLDocument;

class LogoutView extends HTMLDocument {

  public function body() {
    echo new DefaultFormView($this->data->setAttribute('id', 'logoutForm'), $this->html);
    ?>
    <script>document.getElementById("logoutForm").submit();</script>
    <?php

  }

}
