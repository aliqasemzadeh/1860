<?php

namespace App\Console\Commands\System\Administrator;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Throwable;

class CreatePermissionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:administrator:create-permissions-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create and sync panel permissions from language files';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $groups = [
            'user' => null,
            'administrator' => 'administrator',
            'shop' => 'shop',
        ];

        foreach ($groups as $permissionGroup => $roleName) {
            $this->syncGroup($permissionGroup, $roleName);
        }

        $this->info('Permissions synced.');

        return self::SUCCESS;
    }

    protected function syncGroup(string $permissionGroup, ?string $roleName): void
    {
        $permissions = __('permissions.'.$permissionGroup);

        if (! is_array($permissions) || $permissions === []) {
            $this->warn("No permissions found for group [{$permissionGroup}].");

            return;
        }

        foreach ($permissions as $permission => $translate) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        if ($roleName === null) {
            $this->info("Created [{$permissionGroup}] permissions (no role assignment).");

            return;
        }

        try {
            $role = Role::findByName($roleName);
        } catch (Throwable) {
            $this->warn("Role [{$roleName}] not found; permissions for [{$permissionGroup}] were created but not assigned.");

            return;
        }

        foreach ($permissions as $permission => $translate) {
            $role->givePermissionTo($permission);
        }

        $this->info("Synced [{$permissionGroup}] permissions to role [{$roleName}].");
    }
}
