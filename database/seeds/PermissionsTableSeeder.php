<?php

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Permission::insert([
            [
                'module_name' => 'facilities',
                'name' => 'view-any', // manage
            ],
            [
                'module_name' => 'facilities',
                'name' => 'view',
            ],
            [
                'module_name' => 'facilities',
                'name' => 'create',
            ],
            [
                'module_name' => 'facilities',
                'name' => 'update',
            ],
            [
                'module_name' => 'facilities',
                'name' => 'soft-delete', // debar
            ],
            [
                'module_name' => 'facilities',
                'name' => 'restore',
            ],
            [
                'module_name' => 'facilities',
                'name' => 'force-delete', // delete
            ],
            [
                'module_name' => 'facilities',
                'name' => 'view-permissions',
            ],
        ]);

        Permission::insert([
            [
                'module_name' => 'modules',
                'name' => 'view-any', // manage
            ],
            [
                'module_name' => 'modules',
                'name' => 'view',
            ],
            [
                'module_name' => 'modules',
                'name' => 'create',
            ],
            [
                'module_name' => 'modules',
                'name' => 'update',
            ],
            [
                'module_name' => 'modules',
                'name' => 'soft-delete', // debar
            ],
            [
                'module_name' => 'modules',
                'name' => 'restore',
            ],
            [
                'module_name' => 'modules',
                'name' => 'force-delete', // delete
            ],
            [
                'module_name' => 'modules',
                'name' => 'assign-modules', // assign
            ],
        ]);

        Permission::insert([
            [
                'module_name' => 'permissions',
                'name' => 'view-any',
            ],
            [
                'module_name' => 'permissions',
                'name' => 'view',
            ],
            [
                'module_name' => 'permissions',
                'name' => 'create',
            ],
            [
                'module_name' => 'permissions',
                'name' => 'update',
            ],
            [
                'module_name' => 'permissions',
                'name' => 'delete',
            ],
            [
                'module_name' => 'permissions',
                'name' => 'assign-permissions', // assign
            ],
        ]);

        Permission::insert([
            [
                'module_name' => 'roles',
                'name' => 'view-any',
            ],
            [
                'module_name' => 'roles',
                'name' => 'view',
            ],
            [
                'module_name' => 'roles',
                'name' => 'create',
            ],
            [
                'module_name' => 'roles',
                'name' => 'update',
            ],
            [
                'module_name' => 'roles',
                'name' => 'soft-delete',
            ],
            [
                'module_name' => 'roles',
                'name' => 'restore',
            ],
            [
                'module_name' => 'roles',
                'name' => 'force-delete',
            ],
            [
                'module_name' => 'roles',
                'name' => 'view-permissions',
            ],
        ]);

        Permission::insert([
            [
                'module_name' => 'users',
                'name' => 'view-any',
            ],
            [
                'module_name' => 'users',
                'name' => 'view',
            ],
            [
                'module_name' => 'users',
                'name' => 'create',
            ],
            [
                'module_name' => 'users',
                'name' => 'update',
            ],
            [
                'module_name' => 'users',
                'name' => 'soft-delete',
            ],
            [
                'module_name' => 'users',
                'name' => 'restore',
            ],
            [
                'module_name' => 'users',
                'name' => 'force-delete',
            ],
        ]);

        Permission::insert([
            [
                'module_name' => 'pharm-stores',
                'name' => 'view-any',
            ],
            [
                'module_name' => 'pharm-stores',
                'name' => 'view',
            ],
            [
                'module_name' => 'pharm-stores',
                'name' => 'create',
            ],
            [
                'module_name' => 'pharm-stores',
                'name' => 'update',
            ],
            [
                'module_name' => 'pharm-stores',
                'name' => 'soft-delete',
            ],
            [
                'module_name' => 'pharm-stores',
                'name' => 'restore',
            ],
            [
                'module_name' => 'pharm-stores',
                'name' => 'force-delete',
            ],
            [
                'module_name' => 'pharm-stores',
                'name' => 'sync-store-users',
            ],
        ]);

        Permission::insert([
            [
                'module_name' => 'pharm-products',
                'name' => 'view-any',
            ],
            [
                'module_name' => 'pharm-products',
                'name' => 'view',
            ],
            [
                'module_name' => 'pharm-products',
                'name' => 'create',
            ],
            [
                'module_name' => 'pharm-products',
                'name' => 'update',
            ],
            [
                'module_name' => 'pharm-products',
                'name' => 'soft-delete',
            ],
            [
                'module_name' => 'pharm-products',
                'name' => 'restore',
            ],
            [
                'module_name' => 'pharm-products',
                'name' => 'force-delete',
            ],
        ]);

        Permission::insert([
            [
                'module_name' => 'pharm-batches',
                'name' => 'view-any',
            ],
            [
                'module_name' => 'pharm-batches',
                'name' => 'view',
            ],
            [
                'module_name' => 'pharm-batches',
                'name' => 'create',
            ],
            [
                'module_name' => 'pharm-batches',
                'name' => 'delete',
            ],
        ]);
    }
}
