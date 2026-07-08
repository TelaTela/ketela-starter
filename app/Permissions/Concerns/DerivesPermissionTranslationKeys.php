<?php

namespace App\Permissions\Concerns;

use Illuminate\Support\Str;

/**
 * Derives title()/description() translation keys from the enum's own
 * translationGroup() and each case's name, so a new permission case
 * needs zero extra code here.
 */
trait DerivesPermissionTranslationKeys
{
    public function title(): string
    {
        return static::translationGroup() . '.' . $this->translationKey() . '.title';
    }

    public function description(): string
    {
        return static::translationGroup() . '.' . $this->translationKey() . '.description';
    }

    private function translationKey(): string
    {
        return Str::snake($this->name);
    }
}
