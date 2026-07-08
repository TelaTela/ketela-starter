<?php

namespace App\Permissions\Settings\Profile;

use App\Permissions\Concerns\DerivesPermissionTranslationKeys;
use App\Permissions\Contracts\DescribesPermission;

enum ProfilePermission: string implements DescribesPermission
{
    use DerivesPermissionTranslationKeys;

    case ViewAny = 'settings.profile.view-any';
    case UpdateAny = 'settings.profile.update-any';

    public static function translationGroup(): string
    {
        return 'permissions/settings/profile';
    }
}
