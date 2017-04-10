<?php

namespace Manix\Brat\Utility\Users\Models;

use Project\Models\DefaultGateway;

class UserGateway extends DefaultGateway {
  
    const MODEL = User::class;
  
    protected $table = 'manix_users';
    protected $fields = [
        'id', 'password', 'name'
    ];
    protected $ai = 'id';
    protected $pk = ['id'];
    protected $rel = [
        'user' => [UserEmailGateway::class, 'id', 'user_id']
    ];
}
