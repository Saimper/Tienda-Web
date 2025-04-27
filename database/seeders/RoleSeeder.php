<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Seeder;


class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
          // Crear los permisos si no existen
          $permissions = [
            'access ventas',
            'access contabilidad',
            'access inventario', // Logística = Inventario
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Crear los roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $ventasRole = Role::firstOrCreate(['name' => 'ventas']);
        $contabilidadRole = Role::firstOrCreate(['name' => 'contabilidad']);
        $logisticaRole = Role::firstOrCreate(['name' => 'inventario']);

        // Asignar permisos al rol admin (tendrá todos los permisos)
        $adminRole->givePermissionTo(Permission::all());

        // Asignar permisos a los demás roles
        $ventasRole->givePermissionTo('access ventas');
        $contabilidadRole->givePermissionTo('access contabilidad');
        $logisticaRole->givePermissionTo('access inventario');
    }
}
