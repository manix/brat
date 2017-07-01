<?php

namespace Manix\Brat\Utility\Users\Models;

use Manix\Brat\Helpers\Time;
use Project\Models\DefaultGateway;

class UserLoginGateway extends DefaultGateway {

  const MODEL = UserEmail::class;

  protected $table = 'manix_users_logins';
  protected $fields = [
      'user_id', 'ip', 'ua', 'detail'
  ];
  protected $pk = ['user_id', self::TIMESTAMP_CREATED];
  protected $rel = [
      'user' => [UserGateway::class, 'user_id', 'id']
  ];
  protected $timestamps = true;

  public function pack($row) {
    $row = parent::pack($row);

    $row['ip'] = inet_pton($row['ip'] ?? null);
    $row['detail'] = json_encode($row['detail'] ?? []);

    return $row;
  }

  public function unpack($row) {
    $row = parent::unpack($row);

    $row['ip'] = inet_ntop($row['ip'] ?? null);
    $row['detail'] = json_decode($row['detail'] ?? '[]');

    return $row;
  }

}
