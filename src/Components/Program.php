<?php

namespace Manix\Brat\Components;

use Manix\Brat\Components\Cache\CacheGateway;
use Manix\Brat\Components\Cache\FilesystemCache;
use Manix\Brat\Components\Filesystem\Directory;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use Throwable;
use const DEBUG_MODE;
use const PROJECT_PATH;
use function config;

abstract class Program {

  /**
   * Defines the default caching gateway for the application.
   * 
   * @return CacheGateway
   */
  public function constructCacheGateway(): CacheGateway {
    return new FilesystemCache(new Directory(PROJECT_PATH . '/files/cache'));
  }

  abstract public function determineRoute(): string;

  abstract public function determineMethod(): string;

  public function error(Throwable $t) {
    echo $this->respond((new ErrorController($t))->execute('display'));
  }

  abstract public function respond($data);

  /**
   * Send mail using SMTP. This method is chosen by default because it is believed to be 
   * the most utilised and the most secure one.
   * @param mixed $to Can be just a string representing the address or an array with 2 elements - [address, name]
   * @param string $subject
   * @param string $message A view that represents the message to be sent.
   * @param callable $callable A callable that receives the mailer instance
   * before sending, so any custom modifications can be made there.
   * @return bool Whether message has been sent successfully or not.
   */
  public function sendMail($to, $subject, $message, callable $callable = null) {
    $mail = new PHPMailer(true);
    $settings = $_ENV['mail'];

    try {
      //Server settings
      // $mail->SMTPDebug = 2;                                 // Enable verbose debug output
      $mail->CharSet = 'UTF-8';
      $mail->isSMTP();                                      // Set mailer to use SMTP
      $mail->Host = $settings['host'];  // Specify main and backup SMTP servers
      $mail->SMTPAuth = true;                               // Enable SMTP authentication
      $mail->Username = $settings['user'];                 // SMTP username
      $mail->Password = $settings['pass'];                           // SMTP password
      $mail->SMTPSecure = $settings['encryption'];                            // Enable TLS encryption, `ssl` also accepted

      if (DEBUG_MODE) {
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
      }

      $mail->Port = $settings['port'];                                    // TCP port to connect to
      //Recipients
      $mail->setFrom($settings['user'], config('project')['name'] ?? null);
      $mail->addAddress(...(is_array($to) ? $to : [$to]));     // Add a recipient
      //Content
      $mail->isHTML(true);                                  // Set email format to HTML
      $mail->Subject = $subject;
      $mail->Body = $message;
      $mail->AltBody = 'HTML mail not supported.';

      if ($callable !== null) {
        $callable($mail);
      }

      return $mail->send();
    } catch (Exception $e) {
      
    }

    return false;
  }

}
