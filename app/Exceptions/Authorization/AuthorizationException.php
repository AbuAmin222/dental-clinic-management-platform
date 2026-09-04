<?php

declare(strict_types=1);

namespace App\Exceptions\Authorization;

use App\Exceptions\BaseDomainException;

/**
 * High-level marker for every exception belonging to the authorization
 * subsystem (Policies, Permission enums, role/permission sync).
 * Mirrors App\Exceptions\Storage\StorageException: catching this class
 * intercepts any authorization-domain failure regardless of subtype.
 */

abstract class AuthorizationException extends BaseDomainException
{
    //
}
