<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $arrayOfPermissionNames=[
            'user add' , 'user delete' , 'user update' , 'user view' ,
            'roles add' , 'roles delete' , 'roles update' , 'roles view' ,
            'warehouses add' , 'warehouses delete' , 'warehouses update' , 'warehouses view' ,
            'branches add' , 'branches delete' , 'branches update' , 'branches view' ,
            'supplier add' , 'supplier delete' , 'supplier update' , 'supplier view' ,
            'purchase add' , 'purchase delete' , 'purchase update' , 'purchase view' ,
            'sales add' , 'sales delete' , 'sales update' , 'sales view' ,
            'category add' , 'category delete' , 'category update' , 'category view' ,
            'product add' , 'product delete' , 'product update' , 'product view' 
        ];
        $Permissions = collect($arrayOfPermissionNames)->map(function($Permission){
            return['name' => $Permission , 'guard_name' => 'web'];

        });
        Permission::insert($Permissions->toArray());

        $role = Role::create(['name' => 'Super admin'])
        ->givePermissionTo($arrayOfPermissionNames);
    }
}
