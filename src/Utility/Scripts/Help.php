<?php

namespace Manix\Brat\Utility\Scripts;

class Help extends ScriptController {

  public function description() {
    return 'Prints this help screen.';
  }

  public function help($command) {
    return $this->run();
  }

  public function run(...$args) {
    $scripts = config('scripts');

    if (empty($args[0])) {
      $str = 'Usage "help <script>". Available scripts:' . PHP_EOL . PHP_EOL;

      foreach ($scripts as $script => $class) {
        $inst = new $class();

        $str .= $script . "\t\t" . $inst->description() . PHP_EOL;
      }

      return $str;
    } else {
      if (!isset($scripts[$args[0]])) {
        return 'Uknown script';
      }
      
      $inst = new $scripts[$args[0]];
      
      return $inst->help($args[0]);
    }
  }

}
