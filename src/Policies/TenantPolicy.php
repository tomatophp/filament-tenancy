<?php

namespace TomatoPHP\FilamentTenancy\Policies;

use App\Models\User;
use TomatoPHP\FilamentTenancy\Models\Tenant;
use Illuminate\Auth\Access\HandlesAuthorization;

class TenantPolicy
{
    use HandlesAuthorization;

    public function hasRoles(): bool
    {
        return class_exists(\Spatie\Permission\Models\Permission::class);
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny($user): bool
    {
        return $this->hasRoles()? $user->can('view_any_tenant') : true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view($user, Tenant $tenant): bool
    {
        return $this->hasRoles()?  $user->can('view_tenant') : true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create($user): bool
    {
        return $this->hasRoles()?  $user->can('create_tenant') : true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update($user, Tenant $tenant): bool
    {
        return $this->hasRoles()?  $user->can('update_tenant') : true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete($user, Tenant $tenant): bool
    {
        return $this->hasRoles()?  $user->can('delete_tenant') : true;
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny($user): bool
    {
        return $this->hasRoles()?  $user->can('delete_any_tenant') : true;
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete($user, Tenant $tenant): bool
    {
        return $this->hasRoles()?  $user->can('force_delete_tenant'): true;
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny($user): bool
    {
        return $this->hasRoles()?  $user->can('force_delete_any_tenant'): true;
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore($user, Tenant $tenant): bool
    {
        return $this->hasRoles()?  $user->can('restore_tenant'): true;
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny($user): bool
    {
        return $this->hasRoles()?  $user->can('restore_any_tenant'): true;
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate($user, Tenant $tenant): bool
    {
        return $this->hasRoles()?  $user->can('replicate_tenant'): true;
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder($user): bool
    {
        return $this->hasRoles()?  $user->can('reorder_tenant'): true;
    }
}
