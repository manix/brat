<?php

namespace Manix\Brat\Components;

abstract class Plugin {

  /**
   * Defines the admin panel features featured in the plugin.
   * @return array List of FQCNs of feature controllers.
   */
  public function features(): array {
    return [];
  }

  /**
   * Defines the routes required for the operation of the plugin.
   * @return array Associative array of routes, as defined in the routes configuration file.
   */
  public function routes(): array {
    return [];
  }

  /**
   * Specifies the directory which holds necessary files that need to be merged
   * with the project on install.
   * @return mixed NULL or Manix\Brat\Components\Filesystem\Directory instance.
   */
  public function instance() {
    return null;
  }

  public function onbeforeInstall() {

  }

  public function onbeforeUninstall() {

  }

  public function onafterInstall() {

  }

  public function onafterUninstall() {

  }

  public final function install() {
    $this->onbeforeInstall();

    $instance = $this->instance();

    if ($instance !== null) {
      var_dump($instance->copy(PROJECT_PATH . '/..'));
    }

    $routes = $this->routes();

    if (!empty($routes)) {
      $defined = config('routes');

      $this->saveConfigFile('routes', array_merge($defined, $routes));
    }

    $plugins = config('plugins');

    $plugins[] = get_class($this);

    $this->saveConfigFile('plugins', $plugins);

    $this->onafterInstall();
  }

  public final function uninstall() {
    $this->onbeforeUninstall();

    $instance = $this->instance();

    if ($instance !== null) {
      $local = $instance->getPath();
      $project = PROJECT_PATH . '/..';

      foreach ($instance->files() as $file) {
        $path = str_replace($local, $project, $file);
        if (is_file($path)) {
          unlink($path);
        }
      }
    }

    $routes = $this->routes();

    if (!empty($routes)) {
      $defined = config('routes');

      foreach (array_keys($this->routes()) as $route) {
        unset($defined[$route]);
      }

      $this->saveConfigFile('routes', $defined);
    }

    $plugins = config('plugins');

    foreach (array_keys($plugins, get_class($this), true) as $key) {
      unset($plugins[$key]);
    }

    $this->saveConfigFile('plugins', $plugins);

    $this->onafterUninstall();
  }

  protected function saveConfigFile($file, array $data) {
    file_put_contents(PROJECT_PATH . '/config/' . $file . '.php', '<?php return ' . var_export($data, true) . ';');
  }

}
