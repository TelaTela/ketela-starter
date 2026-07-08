<?php

namespace App\Permissions\Settings\Profile;

use App\Permissions\Contracts\DefinesPermissions;

class ProfilePermissionsDefinition implements DefinesPermissions
{
    public static function module(): string
    {
        return 'profile';
    }

    public static function permissions(): string
    {
        return ProfilePermission::class;
    }

    public static function translationGroup(): string
    {
        return ProfilePermission::translationGroup();
    }
}
