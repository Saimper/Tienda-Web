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
          
          $permissions = [
            'access ventas',
            'access contabilidad',
            'access inventario',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

       
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $ventasRole = Role::firstOrCreate(['name' => 'ventas']);
        $contabilidadRole = Role::firstOrCreate(['name' => 'contabilidad']);
        $logisticaRole = Role::firstOrCreate(['name' => 'inventario']);

       
        $adminRole->givePermissionTo(Permission::all());

       
        $ventasRole->givePermissionTo('access ventas');
        $contabilidadRole->givePermissionTo('access contabilidad');
        $logisticaRole->givePermissionTo('access inventario');
    }
}
