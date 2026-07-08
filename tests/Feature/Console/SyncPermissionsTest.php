<?php

namespace Tests\Feature\Console;

use App\Models\Module;
use App\Models\Permission;
use App\Permissions\Concerns\DerivesPermissionTranslationKeys;
use App\Permissions\Contracts\DefinesPermissions;
use App\Permissions\Contracts\DescribesPermission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SyncPermissionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['authorization.permissions' => [FakePermissions::class]]);
    }

    public function test_it_creates_modules_and_permissions_from_definitions()
    {
        $this->artisan('permissions:sync')->assertExitCode(0);

        $module = Module::firstWhere('name', 'fake-feature');
        $this->assertNotNull($module);
        $this->assertSame('Fake Feature', $module->title);

        $this->assertDatabaseHas('permissions', [
            'name' => 'fake.view',
            'title' => 'permissions/fake.view.title',
            'description' => 'permissions/fake.view.description',
        ]);
    }

    public function test_sync_rejects_a_non_slug_module_name()
    {
        config(['authorization.permissions' => [BadlyNamedModulePermissions::class]]);

        $this->expectException(\RuntimeException::class);

        $this->artisan('permissions:sync');
    }

    /**
     * Guards against re-running the command duplicating rows instead
     * of reconciling against what already exists.
     */
    public function test_running_sync_twice_does_not_duplicate_permissions()
    {
        $this->artisan('permissions:sync');
        $this->artisan('permissions:sync');

        $this->assertSame(2, Permission::where('name', 'like', 'fake.%')->count());
    }

    public function test_dry_run_does_not_persist_changes()
    {
        $this->artisan('permissions:sync', ['--dry-run' => true])
            ->assertExitCode(1);

        $this->assertDatabaseMissing('permissions', ['name' => 'fake.view']);
    }

    public function test_dry_run_reports_no_drift_once_synced()
    {
        $this->artisan('permissions:sync');

        $this->artisan('permissions:sync', ['--dry-run' => true])
            ->assertExitCode(0);
    }

    public function test_orphaned_permissions_are_reported_but_not_deleted_by_default()
    {
        $this->artisan('permissions:sync');

        config(['authorization.permissions' => []]);

        $this->artisan('permissions:sync')->assertExitCode(0);

        $this->assertDatabaseHas('permissions', ['name' => 'fake.view']);
    }

    public function test_prune_deletes_orphaned_permissions_when_confirmed()
    {
        $this->artisan('permissions:sync');

        config(['authorization.permissions' => []]);

        $this->artisan('permissions:sync', ['--prune' => true])
            ->expectsConfirmation(
                'Delete 2 orphaned permission(s)? This cannot be undone.',
                'yes',
            );

        $this->assertDatabaseMissing('permissions', ['name' => 'fake.view']);
    }

    public function test_permission_title_falls_back_to_headline_cased_name_when_not_set()
    {
        $module = Module::create(['name' => 'fake-feature']);

        $permission = Permission::create([
            'name' => 'manually.created',
            'guard_name' => 'web',
            'module_id' => $module->id,
        ]);

        $this->assertSame('Manually Created', $permission->title);
    }
}

enum FakePermission: string implements DescribesPermission
{
    use DerivesPermissionTranslationKeys;

    case View = 'fake.view';
    case Manage = 'fake.manage';

    public static function translationGroup(): string
    {
        return 'permissions/fake';
    }
}

class FakePermissions implements DefinesPermissions
{
    public static function module(): string
    {
        return 'fake-feature';
    }

    public static function permissions(): string
    {
        return FakePermission::class;
    }

    public static function translationGroup(): string
    {
        return FakePermission::translationGroup();
    }
}

class BadlyNamedModulePermissions implements DefinesPermissions
{
    public static function module(): string
    {
        return 'Not A Slug';
    }

    public static function permissions(): string
    {
        return FakePermission::class;
    }

    public static function translationGroup(): string
    {
        return FakePermission::translationGroup();
    }
}
