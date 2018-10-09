<?php

namespace Manix\Brat\Utility\CRUD\JavaScript;

use Manix\Brat\Components\Views\HTML\HTMLElement;

class OpenSelector extends HTMLElement {

  public function html() {
    ?>
    <script>
      function openForeignSelector(input) {
        window.selectForeignValue = function (value) {
          input.value = value;
        };
        window.selectForeignValue.url = input.dataset.url;
        window.open(window.selectForeignValue.url, "fS", "width=800,height=600,top=150,left=200");
      }
    </script>
    <?php

  }

}
