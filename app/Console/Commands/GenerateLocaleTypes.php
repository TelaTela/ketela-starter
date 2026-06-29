<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

#[Signature('ts:generate-locale-types')]
#[Description('Generate TypeScript types for supported locales')]
class GenerateLocaleTypes extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $supportedLocales = config('app.supported_locales', ['en']);
        $union = $supportedLocales ? "'".implode("','", $supportedLocales)."'" : 'never';

        $content = <<<TS
// This file is auto-generated - do not edit manually.
// Run `php artisan ts:generate-locale-types` to generate.

export const SUPPORTED_LOCALES = [$union] as const;
export type SupportedLocale = typeof SUPPORTED_LOCALES[number];
TS;

        $path = resource_path('js/types/locales.d.ts');
        File::ensureDirectoryExists(dirname($path));
        File::put($path, $content);

        $this->info('Locale types generated successfully.');
    }
}
