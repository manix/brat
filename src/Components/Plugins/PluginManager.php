<?php

namespace Manix\Brat\Components\Plugins;

use Composer\Installer\PackageEvent;

class PluginManager {

  public static function init($event) {
    $vendorDir = $event->getComposer()->getConfig()->get('vendor-dir');

    if (!is_file($vendorDir . '/autoload.php')) {
      return;
    }

    require $vendorDir . '/autoload.php';

    $package = $event->getOperation()->getPackage();
    $installationManager = $event->getComposer()->getInstallationManager();

    $pluginInstaller = $installationManager->getInstallPath($package) . '/Plugin.php';

    if (file_exists($pluginInstaller)) {
      $installerInstance = require $pluginInstaller;

      if (!($installerInstance instanceof AbstractPlugin)) {
        $installerInstance = new NullPlugin();
      }
    } else {
      $installerInstance = new NullPlugin();
    }


    $installerInstance->vendorDir = $vendorDir;

    return $installerInstance;
  }

  public static function install(PackageEvent $event) {
    self::init($event)->install();
  }

  public static function uninstall(PackageEvent $event) {
    self::init($event)->uninstall();
  }

}
