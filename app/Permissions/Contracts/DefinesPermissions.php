<?php

namespace App\Permissions\Contracts;

/**
 * Contract for a feature's permission definitions. Implementing
 * classes are registered in config/permissions.php and reconciled
 * into the database by `php artisan permissions:sync`.
 */
interface DefinesPermissions
{
    /**
     * The module these permissions are grouped under. permissions:sync
     * creates or reuses a Module row with this name.
     */
    public static function module(): string;

    /**
     * The enum listing this feature's permissions.
     */
    public static function permissions(): string;

    /**
     * Passthrough to the permission enum's own translationGroup().
     */
    public static function translationGroup(): string;
}
