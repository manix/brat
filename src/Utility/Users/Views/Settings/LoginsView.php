<?php

namespace Manix\Brat\Utility\Users\Views\Settings;

use Project\Views\Users\DefaultSettingsLayout;

class LoginsView extends DefaultSettingsLayout {

  public function card() {
    ?>
    <div class="table-responsive">
      <table class="table mb-0">
        <?php foreach ($this->data['logins'] as $login): $browser = get_browser($login->ua); ?>
          <tr>
            <td><?= $login->created ?></td>
            <?php if ($browser ?? null): ?>
              <td><?= $browser->browser ?></td>
              <td><?= $browser->platform ?></td>
            <?php endif; ?>
            <td>
              <a href="http://ipinfo.io/<?= $login->ip ?>" target="_blank">
                <?= $login->ip ?>
              </a>
            </td>
            <td>
              <?php if (empty($login->detail)): ?>
                <i class="fa fa-check"></i>
              <?php elseif ($login->detail === 't'): ?>
                <i class="fa fa-refresh"></i>
              <?php else: ?>
                <?php foreach ($login->detail as $message): ?>
                  <div><?= html($message) ?></div>
                <?php endforeach; ?>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </table>
    </div>
    <?php
  }

}
