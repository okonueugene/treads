<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define permissions used in the MVP (subset)
        $permissions = [
            'vendors.approve',
            'products.approve',
            'products.create',
            'products.delete',
            'orders.view_all',
            'payments.verify',
            'reports.admin',
            'reports.vendor',
            'reviews.moderate',
            'disputes.mediate',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // Roles
        $admin = Role::firstOrCreate(['name' => 'Admin']);
        $vendor = Role::firstOrCreate(['name' => 'Vendor']);
        $customer = Role::firstOrCreate(['name' => 'Customer']);

        // Assign all permissions to admin
        $admin->syncPermissions($permissions);

        // Vendor permissions (subset)
        $vendorPerms = [
            'products.create',
            'products.delete',
            'orders.view_all',
            'reports.vendor'
        ];
        $vendor->syncPermissions($vendorPerms);

        // Customer: no special permissions by default
        $customer->syncPermissions([]);
    }
}
