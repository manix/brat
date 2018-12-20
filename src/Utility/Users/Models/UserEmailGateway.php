<?php

namespace Manix\Brat\Utility\Users\Models;

use Project\Models\DefaultGateway;

class UserEmailGateway extends DefaultGateway {
  
    const MODEL = UserEmail::class;
  
    protected $table = 'manix_users_emails';
    protected $fields = [
        'user_id', 'email', 'verified'
    ];
    protected $pk = ['email'];
    protected $rel = [
        'user' => [UserGateway::class, 'user_id', 'id']
    ];
}
