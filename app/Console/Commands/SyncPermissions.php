<?php

namespace App\Console\Commands;

use App\Models\Module;
use App\Models\Permission;
use App\Permissions\Contracts\DefinesPermissions;
use App\Permissions\Contracts\DescribesPermission;
use Illuminate\Console\Command;
use Spatie\Permission\PermissionRegistrar;
use function Illuminate\Support\enum_value;

class SyncPermissions extends Command
{
    protected $signature = 'permissions:sync
        {--dry-run : Report what would change without writing anything}
        {--prune : Delete permissions no longer declared in any definition class}
        {--force : Skip the confirmation prompt when pruning}';

    protected $description = 'Reconcile the permissions table with the permission definitions declared in code.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        /** @var array<int, class-string<DefinesPermissions>> $definitions */
        $definitions = config('authorization.permissions', []);

        if (empty($definitions)) {
            $this->warn(
                'No permission definitions registered in config/authorization.php'
                .' — every existing permission will be reported as orphaned.',
            );
        }

        $created = [];
        $updated = [];
        $declaredNames = [];

        foreach ($definitions as $definitionClass) {
            /** @var class-string<DefinesPermissions> $definitionClass */
            $moduleName = $definitionClass::module();

            if (! preg_match('/^[a-z][a-z0-9-]*$/', $moduleName)) {
                throw new \RuntimeException(
                    "{$definitionClass}::module() must return a lowercase, kebab-case slug (e.g. 'profile'), got '{$moduleName}'.",
                );
            }

            $enumClass = $definitionClass::permissions();

            if (! is_a($enumClass, \BackedEnum::class, true) || ! is_a($enumClass, DescribesPermission::class, true)) {
                throw new \RuntimeException(
                    "{$definitionClass}::permissions() must return a backed enum implementing DescribesPermission.",
                );
            }

            $module = $this->option('dry-run')
                ? Module::firstWhere('name', $moduleName)
                : Module::firstOrCreate(['name' => $moduleName]);

            foreach ($enumClass::cases() as $case) {
                $name = enum_value($case);
                $title = $case->title();
                $description = $case->description();
                $declaredNames[] = $name;

                $existing = Permission::where('name', $name)
                    ->where('guard_name', 'web')
                    ->first();

                if (! $existing) {
                    $created[] = $name;
                } elseif (
                    $existing->getRawOriginal('title') !== $title
                    || $existing->getRawOriginal('description') !== $description
                    || $existing->module_id !== $module?->id
                ) {
                    $updated[] = $name;
                }

                if (! $this->option('dry-run')) {
                    Permission::updateOrCreate(
                        ['name' => $name, 'guard_name' => 'web'],
                        ['title' => $title, 'description' => $description, 'module_id' => $module->id],
                    );
                }
            }
        }

        $orphaned = Permission::whereNotIn('name', $declaredNames)->pluck('name')->all();

        $this->reportSummary($created, $updated, $orphaned);

        if ($this->option('prune') && ! empty($orphaned) && ! $this->option('dry-run')) {
            $this->pruneOrphans($orphaned);
        }

        if (! $this->option('dry-run')) {
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        }

        $hasDrift = ! empty($created) || ! empty($updated) || ! empty($orphaned);

        return $this->option('dry-run') && $hasDrift ? self::FAILURE : self::SUCCESS;
    }

    /**
     * @param array<int, string> $created
     * @param array<int, string> $updated
     * @param array<int, string> $orphaned
     */
    private function reportSummary(array $created, array $updated, array $orphaned): void
    {
        $this->components->twoColumnDetail('Created', (string) count($created));
        foreach ($created as $name) {
            $this->line("  <fg=green>+</> {$name}");
        }

        $this->components->twoColumnDetail('Updated', (string) count($updated));
        foreach ($updated as $name) {
            $this->line("  <fg=yellow>~</> {$name}");
        }

        $this->components->twoColumnDetail('Orphaned (in DB, not declared in code)', (string) count($orphaned));
        foreach ($orphaned as $name) {
            $this->line("  <fg=red>-</> {$name}");
        }

        if (empty($created) && empty($updated) && empty($orphaned)) {
            $this->components->info('Permissions already match code definitions.');
        }
    }

    /**
     * @param array<int, string> $orphaned
     */
    private function pruneOrphans(array $orphaned): void
    {
        if (! $this->option('force') && ! $this->confirm(
            'Delete '.count($orphaned).' orphaned permission(s)? This cannot be undone.',
        )) {
            $this->warn('Skipped pruning.');
            return;
        }

        Permission::whereIn('name', $orphaned)->delete();
        $this->components->info('Pruned '.count($orphaned).' orphaned permission(s).');
    }
}
