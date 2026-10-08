<?php

namespace Manix\Brat\Utility\Users\Views\Settings;

use Manix\Brat\Helpers\FormViews\DefaultFormView;
use Project\Views\Users\DefaultSettingsLayout;
use function html;

/**
 * The QR code is the project's: once the page has loaded, this calls
 * window.mfaQR(element, uri) if the project defines it, to draw the otpauth:// address
 * into the element. Without it the secret and a link to the app are still shown.
 */
class MFAView extends DefaultSettingsLayout {

  public function card() {
    $labels = [
        'code' => $this->t8('mfaCode'),
        'currentPassword' => $this->t8('currPass')
    ];
    ?>
    <div class="card-body">
      <?php if (isset($this->data['totp'])): ?>
        <p><?= $this->t8('mfaOn') ?> <?= html($this->data['totp']->created) ?></p>
      <?php else: ?>
        <p><?= $this->t8('mfaSetupHelp') ?></p>
        <div id="mfa_qr" class="mb-3"></div>
        <p>
          <?= $this->t8('mfaSecret') ?> <code><?= html($this->data['secret']) ?></code><br/>
          <a href="<?= html($this->data['uri']) ?>"><?= $this->t8('mfaOpenApp') ?></a>
        </p>
        <script>
          document.addEventListener('DOMContentLoaded', function () {
            if (window.mfaQR) {
              window.mfaQR(document.getElementById('mfa_qr'), <?= json_encode($this->data['uri']) ?>);
            }
          });
        </script>
      <?php endif ?>

      <?= new DefaultFormView($this->data['form'], $this->html, $labels) ?>
    </div>
    <?php
  }

}
