<?php

namespace Manix\Brat\Utility\CRUD\JavaScript;

use Manix\Brat\Components\Views\HTML\HTMLElement;

class SelectValueForOpener extends HTMLElement {

  public function html() {
    ?>
    <script>
      if (window.opener || window.parent !== window) {
        (function (w, owner) {
          if (owner.selectForeignValue.url.indexOf(location.href.replace(location.search, "")) !== 0) {
            location.href = owner.selectForeignValue.url;
          }

          var records = w.document.querySelectorAll(".table-crud tbody tr");
          var select = function (e) {
            e.preventDefault();
            owner.selectForeignValue(this.dataset.pk.trim());
            w.close();
          };
          for (var i = 0, l = records.length; i < l; i++) {
            records[i].onclick = select;
          }
        })(window, window.opener || window.parent);


        document.addEventListener("keypress", function (e) {
          if (e.key === "Escape") {
            window.close();
          }
        });

        document.querySelector('input[name="query"]').focus();
      }
    </script>
    <?php

  }

}
