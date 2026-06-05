<?php

namespace Manix\Brat\Utility\Users\Views\Settings;

use Project\Views\Users\DefaultSettingsLayout;

class LoginsView extends DefaultSettingsLayout {

  public function card() {
    ?>
    <div class="table-responsive">
      <table class="table mb-0">
        <?php foreach ($this->data['logins'] as $login): $browser = $this->parseUserAgent($login->ua); ?>
          <tr>
            <td><?= $login->created ?></td>
            <?php if ($browser ?? null): ?>
              <td><?= $browser['browser'] ?></td>
              <td><?= $browser['platform'] ?></td>
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

  function parseUserAgent($userAgent) {
      $result = [
          'browser' => 'Unknown',
          'browser_version' => 'Unknown',
          'os' => 'Unknown',
          'os_version' => 'Unknown',
          'device_type' => 'Unknown',
          'platform' => 'Unknown'
      ];

      // Detect Operating System and Platform
      if (preg_match('/Windows NT 10\.0/', $userAgent)) {
          $result['os'] = 'Windows';
          $result['os_version'] = '10';
          $result['platform'] = 'Windows';
      } elseif (preg_match('/Windows NT 6\.3/', $userAgent)) {
          $result['os'] = 'Windows';
          $result['os_version'] = '8.1';
          $result['platform'] = 'Windows';
      } elseif (preg_match('/Windows NT 6\.2/', $userAgent)) {
          $result['os'] = 'Windows';
          $result['os_version'] = '8';
          $result['platform'] = 'Windows';
      } elseif (preg_match('/Windows NT 6\.1/', $userAgent)) {
          $result['os'] = 'Windows';
          $result['os_version'] = '7';
          $result['platform'] = 'Windows';
      } elseif (preg_match('/Mac OS X ([0-9._]+)/', $userAgent, $matches)) {
          $result['os'] = 'macOS';
          $result['os_version'] = str_replace('_', '.', $matches[1]);
          $result['platform'] = 'macOS';
      } elseif (preg_match('/iPhone OS ([0-9_]+)/', $userAgent, $matches)) {
          $result['os'] = 'iOS';
          $result['os_version'] = str_replace('_', '.', $matches[1]);
          $result['platform'] = 'iOS';
          $result['device_type'] = 'Mobile';
      } elseif (preg_match('/iPad.*OS ([0-9_]+)/', $userAgent, $matches)) {
          $result['os'] = 'iPadOS';
          $result['os_version'] = str_replace('_', '.', $matches[1]);
          $result['platform'] = 'iPadOS';
          $result['device_type'] = 'Tablet';
      } elseif (preg_match('/Android ([0-9.]+)/', $userAgent, $matches)) {
          $result['os'] = 'Android';
          $result['os_version'] = $matches[1];
          $result['platform'] = 'Android';
      } elseif (preg_match('/Linux/', $userAgent)) {
          $result['os'] = 'Linux';
          $result['platform'] = 'Linux';
      }

      // Detect Browser
      if (preg_match('/Edg\/([0-9.]+)/', $userAgent, $matches)) {
          $result['browser'] = 'Edge';
          $result['browser_version'] = $matches[1];
      } elseif (preg_match('/Edge\/([0-9.]+)/', $userAgent, $matches)) {
          $result['browser'] = 'Edge (Legacy)';
          $result['browser_version'] = $matches[1];
      } elseif (preg_match('/Chrome\/([0-9.]+)/', $userAgent, $matches)) {
          $result['browser'] = 'Chrome';
          $result['browser_version'] = $matches[1];
      } elseif (preg_match('/Safari\/([0-9.]+)/', $userAgent, $matches)) {
          $result['browser'] = 'Safari';
          $result['browser_version'] = $matches[1];
      } elseif (preg_match('/Firefox\/([0-9.]+)/', $userAgent, $matches)) {
          $result['browser'] = 'Firefox';
          $result['browser_version'] = $matches[1];
      } elseif (preg_match('/MSIE ([0-9.]+)/', $userAgent, $matches)) {
          $result['browser'] = 'Internet Explorer';
          $result['browser_version'] = $matches[1];
      } elseif (preg_match('/Trident.*rv:([0-9.]+)/', $userAgent, $matches)) {
          $result['browser'] = 'Internet Explorer';
          $result['browser_version'] = $matches[1];
      }

      // Detect Device Type (if not already set by OS detection)
      if ($result['device_type'] === 'Unknown') {
          if (preg_match('/iPad/', $userAgent)) {
              $result['device_type'] = 'Tablet';
          } elseif (preg_match('/Mobile|Android|iPhone|iPod/', $userAgent)) {
              $result['device_type'] = 'Mobile';
          } else {
              $result['device_type'] = 'Desktop';
          }
      }

      return $result;
  }

}
