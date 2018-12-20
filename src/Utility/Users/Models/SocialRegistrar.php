<?php

namespace Manix\Brat\Utility\Users\Models;

use Exception;
use Manix\Brat\Helpers\Image;
use Manix\Brat\Helpers\Redirect;
use Project\Traits\Users\UserGatewayFactory;
use function route;

class SocialRegistrar {

  use UserGatewayFactory;

  public function register($data) {
    if (empty($data['email']) || empty($data['name'])) {
      throw new Exception('Can not register, email or name missing', 500);
    }

    $email = $data['email'];

    $egate = $this->getEmailGateway();
    $ugate = $this->getUserGateway();
    $record = $egate->find($email)->first();

    if (!$record) {
      $user = $this->constructUser($data);

      if (!$ugate->persist($user)) {
        throw new Exception('Unexpected', 500);
      }

      $email = $this->constructEmail($user, $data);

      if (!$egate->persist($email)) {
        throw new Exception('Unexpected', 500);
      }

      if ($data['img']) {
        $this->updatePhoto($ugate, $user, $data['img']);
      }
    } else {
      $user = $ugate->find($record->user_id)->first();
    }

    Auth::register($user);

    return new Redirect(route(get_class(Auth::getManager()->getLoginController())));
  }

  protected function constructUser($data) {
    $user = new User([
        'name' => $data['name']
    ]);
    $user->setPassword(random_bytes(16));
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
