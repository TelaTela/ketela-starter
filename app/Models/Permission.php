<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission as SpatiePermission;

/**
 * @property int $module_id
 * @property-read string $title
 * @property-read string|null $description
 */
#[Fillable(['name', 'guard_name', 'module_id', 'title','description'])]
class Permission extends SpatiePermission
{
    /**
     * @return BelongsTo<Module,$this>
     */
    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    /**
     * Resolves the stored title as a translation key. Falls back to
     * the raw permission name for rows created outside the normal
     * permissions:sync flow (e.g. a package or direct Eloquent call)
     * that never had a title key assigned.
     *
     * @return Attribute<string, never>
     */
    protected function title(): Attribute
    {
        return Attribute::make(
            get: function (?string $value): string {
                if (! $value) {
                    return Str::headline(str_replace('.', ' ', $this->name));
                }

                $resolved = __($value);

                return is_string($resolved) ? $resolved : $value;
            },
        );
    }

    /**
     * Resolves the stored description as a translation key.
     *
     * @return Attribute<string|null, never>
     */
    protected function description(): Attribute
    {
        return Attribute::make(
            get: function (?string $value): ?string {
                if (! $value) {
                    return null;
                }

                $resolved = __($value);

                return is_string($resolved) ? $resolved : $value;
            },
        );
    }
}
