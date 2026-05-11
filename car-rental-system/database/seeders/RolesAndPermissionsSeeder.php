<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ─── Permissions ──────────────────────────────────────────────────────────

        $permissions = [
            // Vehicle management
            'view vehicles',
            'create vehicles',
            'edit vehicles',
            'delete vehicles',

            // Category management
            'view vehicle_categories',
            'create vehicle_categories',
            'edit vehicle_categories',
            'delete vehicle_categories',

            // Booking management
            'view all bookings',
            'view own bookings',
            'create bookings',
            'cancel bookings',
            'confirm bookings',

            // Payment management
            'view payments',
            'process payments',
            'refund payments',

            // User management
            'view users',
            'create users',
            'edit users',
            'delete users',

            // Chat
            'view all chats',
            'send messages',

            // Pricing
            'manage pricing',

            // Reports
            'view reports',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        // ─── Roles ────────────────────────────────────────────────────────────────

        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $customerRole = Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'web']);

        // Admin gets all permissions
        $adminRole->syncPermissions(Permission::all());

        // Customer gets limited permissions
        $customerRole->syncPermissions([
            'view vehicles',
            'view vehicle_categories',
            'view own bookings',
            'create bookings',
            'cancel bookings',
            'view payments',
            'send messages',
        ]);

        // ─── Admin User ───────────────────────────────────────────────────────────

        $admin = User::firstOrCreate(
            ['email' => 'admin@carrental.com'],
            [
                'name' => 'System Admin',
                'password' => Hash::make('Admin@1234'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        $admin->assignRole('admin');

        $this->command->info('✅ Roles and permissions seeded successfully.');
        $this->command->info('✅ Admin user created successfully.');
        $this->command->info('   Email:    admin@carrental.com');
        $this->command->info('   Password: Admin@1234');
    }
}