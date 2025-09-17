<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Limpia cache de permisos
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Permisos (granulares)
        $perms = [
            'recipes.create','recipes.view','recipes.update','recipes.delete',
            'ingredients.create','ingredients.view','ingredients.update','ingredients.delete',
            'daily-check.run'
        ];
        foreach ($perms as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'api']);
        }

        // Roles
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'api']);
        $user  = Role::firstOrCreate(['name' => 'user',  'guard_name' => 'api']);

        // Asignaciones: admin => todos, user => permisos limitados
        $admin->syncPermissions(Permission::all());
        $user->syncPermissions(['recipes.create','recipes.view','ingredients.create','ingredients.view','daily-check.run']);
    }
}
