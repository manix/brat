<?php

namespace Manix\Brat\Utility\CRUD\JavaScript;

use Manix\Brat\Components\Views\HTML\HTMLElement;

class SelectValueForOpener extends HTMLElement {

  public function html() {
    ?>
    <script>
      if (window.opener || window.parent !== window) {
        (function (pki, w, owner) {
          if (owner.selectForeignValue.url.indexOf(location.href.replace(location.search, "")) !== 0) {
            location.href = owner.selectForeignValue.url;
          }

          var pk = w.document.querySelector(".table-crud thead th.pk");
          while ((pk = pk.previousElementSibling) !== null) {
            pki++;
          }
          var records = w.document.querySelectorAll(".table-crud tbody tr");
          var select = function (e) {
            e.preventDefault();
            owner.selectForeignValue(this.children[pki].textContent.trim());
            w.close();
          };
          for (var i = 0, l = records.length; i < l; i++) {
            records[i].onclick = select;
          }
        })(0, window, window.opener || window.parent);


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
