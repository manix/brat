<?php

namespace Manix\Brat\Utility\Users\Models;

use Exception;
use Manix\Brat\Helpers\Image;
use PDOException;
use Project\Traits\Users\UserGatewayFactory;

class SocialRegistrar {

  use UserGatewayFactory;

  /**
   * The user to log in with the provider's $data: the one with its email, or a new one. The existing one only
   * when the provider verified the email ($data['verified']), else an account there under someone's email
   * would log in as them.
   * @return User
   */
  public function register($data) {
    if (empty($data['email']) || empty($data['name'])) {
      throw new Exception('Can not register, email or name missing', 500);
    }

    $egate = $this->getEmailGateway();
    $ugate = $this->getUserGateway();
    $record = $egate->find($data['email'])->first();

    if ($record) {
      if (empty($data['verified'])) {
        throw new Exception('The email is registered, log in with the password', 403);
      }

      return $ugate->find($record->user_id)->first();
    }

    $pdo = $ugate->getPDO();
    $user = $this->constructUser($data);

    $pdo->beginTransaction();

    if (!$ugate->persist($user)) {
      $pdo->rollBack();

      throw new Exception('Unexpected', 500);
    }

    // a plain insert: an email registered since the check above fails here instead of moving to the new user
    try {
      $egate->insert($this->constructEmail($user, $data));
    } catch (PDOException $e) {
      $pdo->rollBack();

      if (($e->errorInfo[1] ?? null) !== 1062) {
        throw $e;
      }

      throw new Exception('The email is registered, log in with the password', 403);
    }

    $pdo->commit();

    if ($data['img']) {
      $this->updatePhoto($ugate, $user, $data['img']);
    }

    return $user;
  }

  protected function constructUser($data) {
    $user = new User([
        'name' => $data['name']
    ]);
    // hex: bcrypt stops at the first zero byte, which random bytes can have early
    $user->setPassword(bin2hex(random_bytes(16)));
    return $user;
  }

  protected function constructEmail($user, $data) {
    $email = new UserEmail([
        'user_id' => $user->id,
        'email' => $data['email']
    ]);
    $email->validate();
    return $email;
  }

  protected function updatePhoto($gate, $user, $url) {
    $img = Image::fromString(file_get_contents($url));
    $img->setType(IMAGETYPE_PNG);
    $img->setFile(PUBLIC_PATH . '/assets/images/users/hd/' . $user->id);
    $img->save();

    $thumb = clone $img;
    $thumb->resize(128, 128);
    $thumb->setFile(PUBLIC_PATH . '/assets/images/users/thumb/' . $user->id);
    $thumb->save();

    $user->photo_rev++;

    $gate->persist($user);
    Auth::updateCache($user);
  }

}
