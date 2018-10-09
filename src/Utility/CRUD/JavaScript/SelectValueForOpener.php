<?php

namespace Manix\Brat\Utility\CRUD\JavaScript;

use Manix\Brat\Components\Views\HTML\HTMLElement;

class SelectValueForOpener extends HTMLElement {

  public function html() {
    ?>
    <script>
      if (window.opener) {
        if (window.opener.selectForeignValue.url.indexOf(location.href.replace(location.search, "")) !== 0) {
          location.href = window.opener.selectForeignValue.url;
        }

        (function (pki, w, select) {
          var pk = w.document.querySelector(".table-crud thead th.pk");
          while ((pk = pk.previousElementSibling) !== null) {
            pki++;
          }
          var records = w.document.querySelectorAll(".table-crud tbody tr");
          var select = function (e) {
            e.preventDefault();
            w.opener.selectForeignValue(this.children[pki].textContent.trim());
            w.close();
          };
          for (var i = 0, l = records.length; i < l; i++) {
            records[i].onclick = select;
          }
        })(0, window);
      }
    </script>
    <?php

  }

}
