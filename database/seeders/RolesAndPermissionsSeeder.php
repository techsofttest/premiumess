<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use App\Models\User;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Permissions
        $permissions = [
            // Orders permissions
            'orders.view',
            'orders.create',
            'orders.update',
            'orders.delete',
            'orders.export',
            'orders.print',
            'orders.cancel',
            'orders.refund',
            'orders.assign',

            // Products permissions
            'products.view',
            'products.create',
            'products.update',
            'products.delete',

            // Categories / Catalog permissions
            'categories.view',
            'categories.create',
            'categories.update',
            'categories.delete',

            // Customers permissions
            'customers.view',
            'customers.update',

            // Reports permissions
            'reports.view',

            // CMS / Contents permissions
            'cms.view',
            'cms.update',

            // Advertisements permissions
            'advertisements.view',
            'advertisements.update',

            // Settings permissions
            'settings.manage',

            // Users permissions
            'users.view',
            'users.create',
            'users.update',
            'users.delete',

            // Staff users permissions
            'staff-users.view',
            'staff-users.create',
            'staff-users.update',
            'staff-users.delete',

            // Delivery operations permissions
            'delivery.manage',
            'delivery.driver',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        // Create Roles and Assign Permissions
        
        // Super Admin Role
        $superAdminRole = Role::findOrCreate('Super Admin');
        // Super Admin gets all permissions dynamically via Gate::before, but we can assign them too
        $superAdminRole->syncPermissions(Permission::all());

        // Staff Role
        $staffRole = Role::findOrCreate('Staff');
        $staffRole->syncPermissions([
            'orders.view',
            'orders.update',
            'orders.print',
            'delivery.driver',
        ]);

        // Assign Roles to existing admin / superadmin users
        $admins = User::whereIn('role', ['admin', 'superadmin'])->get();
        foreach ($admins as $admin) {
            $admin->assignRole('Super Admin');
        }

        // Create a test staff user if they don't exist
        $staffUser = User::firstOrCreate(
            ['email' => 'staff@example.com'],
            [
                'name' => 'Test Staff',
                'password' => bcrypt('password'),
                'role' => 'staff',
            ]
        );
        $staffUser->assignRole('Staff');
        
        // Let's also make sure we have a super admin user for testing
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'),
                'role' => 'admin',
            ]
        );
        $adminUser->assignRole('Super Admin');
    }
}
