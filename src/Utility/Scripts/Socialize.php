<?php

namespace Manix\Brat\Utility\Scripts;

use Manix\Brat\Components\Filesystem\File;

class Socialize extends ScriptController {

  public function description() {
    return 'Installs composer dependencies for social logins.';
  }

  public function help($command) {
    return $this->run();
  }

  public function run(...$args) {
    exec('cd ' . PROJECT_PATH . '/.. && composer require league/oauth2-facebook league/oauth2-github league/oauth2-google league/oauth2-instagram league/oauth2-linkedin stevenmaguire/oauth2-microsoft');

    file_put_contents(new File(PROJECT_PATH . '/config/social.php'), $this->configTemplate());
  }

  protected function configTemplate() {
    return <<<SOC
<?php

return [
  'registrar' => \Manix\Brat\Utility\Users\Models\SocialRegistrar::class,

  \Manix\Brat\Utility\Users\Controllers\Social\Facebook::class => [
    'clientId'          => '',
    'clientSecret'      => '',
    'graphApiVersion'   => 'v2.10',
  ],
  \Manix\Brat\Utility\Users\Controllers\Social\Linkedin::class => [
    'clientId'          => '',
    'clientSecret'      => ''
  ],
  \Manix\Brat\Utility\Users\Controllers\Social\Github::class => [
    'clientId'          => '',
    'clientSecret'      => ''
  ],
  \Manix\Brat\Utility\Users\Controllers\Social\Google::class => [
    'clientId'          => '',
    'clientSecret'      => ''
  ],
  \Manix\Brat\Utility\Users\Controllers\Social\Microsoft::class => [
    'clientId'          => '',
    'clientSecret'      => ''
  ]
];
SOC;
  }

}
