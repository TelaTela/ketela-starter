<?php

namespace App\Permissions\Contracts;

interface DescribesPermission
{
    /** Lang group this permission's title/description live under. */
    public static function translationGroup(): string;

    public function title(): string;

    public function description(): string;
}
