<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roleAdmins = Role::firstOrCreate(['name' => 'admins']);
        Role::firstOrCreate(['name' => 'patients']);
        Role::firstOrCreate(['name' => 'doctors']);

        Permission::firstOrCreate(['name' => 'adminpanel_see']);

        $roleAdmins->givePermissionTo(Permission::all());
    }
}
