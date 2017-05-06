<?php

namespace Manix\Brat\Components\Plugins;

use Composer\Installer\PackageEvent;

class PluginManager {

  public static function init($event) {
    $vendorDir = $event->getComposer()->getConfig()->get('vendor-dir');

    if (!is_file($vendorDir . '/autoload.php')) {
      return new NullPlugin();
    }

    require $vendorDir . '/autoload.php';

    $package = $event->getOperation()->getPackage();
    $installationManager = $event->getComposer()->getInstallationManager();

    $pluginInstaller = $installationManager->getInstallPath($package) . '/Plugin.php';

    if (file_exists($pluginInstaller)) {
      $installerInstance = require $pluginInstaller;

      if (!($installerInstance instanceof AbstractPlugin)) {
        return new NullPlugin();
      }
    } else {
      return new NullPlugin();
    }
  }

  public static function install(PackageEvent $event) {
    self::init($event)->install();
  }

  public static function uninstall(PackageEvent $event) {
    self::init($event)->uninstall();
  }

}
