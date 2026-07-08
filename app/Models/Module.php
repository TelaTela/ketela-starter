<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property-read string $title
 */
#[Fillable(['name'])]
class Module extends Model
{
    /**
     * @return HasMany<Permission,$this>
     */
    public function permissions(): HasMany
    {
        return $this->hasMany(Permission::class);
    }

    /**
     * Translated display text, resolved from lang/{locale}/modules.php
     * using this module's slug.
     *
     * @return Attribute<string, never>
     */
    protected function title(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                $resolved = __('modules.'.$this->name);

                return is_string($resolved) ? $resolved : $this->name;
            },
        );
    }
}
