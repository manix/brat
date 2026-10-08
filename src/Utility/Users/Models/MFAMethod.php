<?php

namespace Manix\Brat\Utility\Users\Models;

use Manix\Brat\Components\Model;

/**
 * A second login factor, such as an authenticator app (MFATotp), an email or an SMS code.
 * A user's set up method is a record of MFAGateway: `data` holds what the method needs
 * (the app's secret, an address), `used` what it needs to refuse a code twice.
 * The methods a project offers are listed in MFAMethods.
 */
interface MFAMethod {

  /**
   * Called when the login asks for the code: a method that sends the code (email, SMS)
   * sends it here. An authenticator app needs nothing.
   */
  public function challenge(Model $record);

  /**
   * Whether $code is right for $record. On success the method updates $record->used, and
   * the caller stores it with MFAGateway::markUsed, which refuses the same code twice.
   */
  public function verify(Model $record, string $code): bool;
}
