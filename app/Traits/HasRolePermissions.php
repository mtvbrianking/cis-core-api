<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Collection;

/**
 * Has role-permissions trait.
 */
trait HasRolePermissions
{
    // helpers

    /**
     * Determine if a user has any of the given roles.
     *
     * @param string ...$roles
     *
     * @return bool
     */
    public function hasRole(...$roles) : bool
    {
        if (! $user_role = $this->role) {
            return false;
        }

        foreach ($roles as $role) {
            if ($user_role->name === $role) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if a user has a given permission.
     *
     * @param \App\Models\Permissions $permission
     *
     * @return bool
     */
    public function hasPermission($permission) : bool
    {
        if (! $role = $this->role) {
            return false;
        }

        return (bool) $role->permissions->where('id', $permission->id)->count();
    }

    /**
     * Determine if a user has a given permission on module.
     *
     * @param string $permission
     * @param string $module
     *
     * @return bool
     */
    public function hasPermissionTo($permission, $module) : bool
    {
        if (! $role = $this->role) {
            return false;
        }

        return (bool) $role->permissions->where('module_name', $module)->where('name', $permission)->count();
    }

    /**
     * Determine if a user has any permission on a given module.
     *
     * @param string $module
     *
     * @return bool
     */
    public function hasAnyPermissionOn($module) : bool
    {
        if (! $role = $this->role) {
            return false;
        }

        return (bool) $role->permissions->where('module_name', $module)->count();
    }

    /**
     * Determine if a user has any of the given permissions.
     *
     * @param array  $permissions
     * @param string $module
     *
     * @return bool
     */
    public function hasAnyPermissionOf(array $permissions, $module) : bool
    {
        if (! $role = $this->role) {
            return false;
        }

        return (bool) $role->permissions->where('module_name', $module)->whereIn('name', $permission)->count();
    }

    /**
     * Convert to Permission models.
     *
     * ```php
     * convertToPermissionModels('create');
     * convertToPermissionModels('create', 'App\Models\User');
     * ```
     *
     * @param string|array|\App\Models\Permission|\Illuminate\Support\Collection $permissions
     * @param string                                                             $module
     *
     * @return array
     */
    protected function convertToPermissionModels($permissions, $module = null)  : array
    {
        if ($permissions instanceof Collection) {
            $permissions = $permissions->all();
        }

        $permissions = array_wrap($permissions);

        return array_map(function ($permission) use ($module) {
            if ($permission instanceof Permission) {
                return $permission;
            }

            $query = Permission::where('name', $permission);

            if ($module) {
                $query->where('module_name', $module);
            }

            return $query->first();
        }, $permissions);
    }

    // scopes

    /**
     * Users with given role.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \App\Models\Role                      $role
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeHasRole($query, $role)
    {
        return $query->whereHas('role', function ($query) use ($role) {
            $query->where('roles.id', $role->id);
        });
    }

    /**
     * Scope query to include only results with given role(s).
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \App\Models\Role                      ...$roles
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeHasRoles($query, ...$roles)
    {
        return $query->whereHas('role', function ($query) use ($roles) {
            $query->where(function ($query) use ($roles) {
                foreach ($roles as $role) {
                    $query->orWhere('roles.id', $role->id);
                }
            });
        });
    }

    /**
     * Scope query to include only results with given permission.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \App\Models\Permission                $permission
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeHasPermission($query, $permission)
    {
        return $query->whereHas('role', function ($query) use ($permission) {
            foreach ($permission->roles as $role) {
                $query->where('roles.id', $role->id);
            }
        });
    }
}
