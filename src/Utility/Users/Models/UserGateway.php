<?php

namespace Manix\Brat\Utility\Users\Models;

use Project\Models\DefaultGateway;

class UserGateway extends DefaultGateway {
  
    const MODEL = User::class;
  
    protected $table = 'manix_users';
    protected $fields = [
        'id', 'password', 'name', 'photo_rev'
    ];
    protected $ai = 'id';
    protected $pk = ['id'];
    protected $rel = [
        'emails' => [UserEmailGateway::class, 'id', 'user_id'],
        'logins' => [UserLoginGateway::class, 'id', 'user_id'],
        'tokens' => [UserTokenGateway::class, 'id', 'user_id'],
    ];
    protected $timestamps = true;
    
}
