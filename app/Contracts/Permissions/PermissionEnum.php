<?php

declare(strict_types=1);

namespace App\Contracts\Permissions;

/**
 * Marker + behavior contract every domain Permission enum must implement.
 * Extending BackedEnum guarantees ->value exists on any implementer,
 * while label()/group() carry the seeder metadata that used to live
 * as raw array literals in the old flat seeder.
 */
interface PermissionEnum extends \BackedEnum
{
    /** Human-readable display_name for the permissions table. */
    public function label(): string;

    /** The `group` column value in the permissions table. */
    public function group(): string;
}
