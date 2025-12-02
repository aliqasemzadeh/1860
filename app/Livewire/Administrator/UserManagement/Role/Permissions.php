<?php

namespace App\Livewire\Administrator\UserManagement\Role;

use Flux\Flux;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class Permissions extends Component
{
    use WithPagination;

    public Role $role;

    public $search;

    public function mount($id = 1)
    {
        $this->role = Role::findById($id);
    }

    #[On('administrator.user-management.role.permissions.assign-data')]
    public function assignData(int $id): void
    {
        $this->role = Role::findById($id);
        Flux::modal('administrator.user-management.role.permissions.modal')->show();
    }

    public function assign(Permission $permission)
    {
        $this->role->givePermissionTo($permission->name);
        $this->dispatch('administrator.user-management.role.permissions');
    }

    public function delete(Permission $permission): void
    {
        $this->role->revokePermissionTo($permission->name);
        $this->dispatch('administrator.user-management.role.permissions');
    }

    #[On('administrator.user-management.role.permissions.render')]
    public function render()
    {
        $this->authorize('administrator_user_role_permissions');
        if ($this->search != '') {
            $permissions = Permission::where('name', 'like', '%'.$this->search.'%')->paginate();
        } else {
            $permissions = Permission::paginate();
        }

        return view('livewire.administrator.user-management.role.permissions', compact('permissions'));
    }
}
