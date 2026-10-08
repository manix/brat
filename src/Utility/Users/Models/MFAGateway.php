<?php

namespace Manix\Brat\Utility\Users\Models;

use Manix\Brat\Components\Model;
use Project\Models\DefaultGateway;

/**
 * The MFA methods users have set up, one row per user and method (see MFAMethod).
 *
 * CREATE TABLE `manix_users_mfa` (
 *   `user_id` int(10) UNSIGNED NOT NULL,
 *   `method` varchar(16) NOT NULL,
 *   `data` varchar(255) NOT NULL,
 *   `used` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
 *   `created` datetime NOT NULL,
 *   `updated` datetime NOT NULL,
 *   PRIMARY KEY (`user_id`, `method`)
 * );
 */
class MFAGateway extends DefaultGateway {

  protected $table = 'manix_users_mfa';
  protected $fields = [
      'user_id', 'method', 'data', 'used'
  ];
  protected $pk = ['user_id', 'method'];
  protected $rel = [
      'user' => [UserGateway::class, 'user_id', 'id']
  ];
  protected $timestamps = true;

  /**
   * Stores $record->used only if it is still $previous in the table, so of two requests
   * with the same code at the same time only one gets it accepted.
   * @return bool Whether it was stored.
   */
  public function markUsed(Model $record, $previous): bool {
    $stmt = $this->pdo->prepare("UPDATE {$this->table} SET used = ? WHERE user_id = ? AND method = ? AND used = ?");
    $stmt->execute([$record->used, $record->user_id, $record->method, (int)$previous]);

    return $stmt->rowCount() === 1;
  }

}
