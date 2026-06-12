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

        const url = new URL(input.dataset.url);
        const search = new URLSearchParams(url.search);
        search.set("fsel", 1);
        url.search = search;
        
        window.selectForeignValue.url = url.toString()
        window.foreignSelector = window.open(window.selectForeignValue.url, "fS", "width=800,height=600,top=150,left=200");

        return false;
      }
    </script>
    <?php

  }

}
