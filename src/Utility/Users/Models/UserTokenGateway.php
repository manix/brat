<?php

namespace Manix\Brat\Utility\Users\Models;

use Project\Models\DefaultGateway;

class UserTokenGateway extends DefaultGateway {
  
    const MODEL = UserToken::class;
  
    protected $table = 'manix_users_tokens';
    protected $fields = [
        'id', 'hash', 'user_id', 'ua'
    ];
    protected $ai = 'id';
    protected $pk = ['id'];
    protected $rel = [
        'user' => [UserGateway::class, 'user_id', 'id']
    ];
}
