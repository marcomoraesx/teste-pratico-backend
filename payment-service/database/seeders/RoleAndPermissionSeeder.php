<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $permissions = [
            'gateway.activate',
            'gateway.deactivate',
            'gateway.change-priority',
            'user.create',
            'user.view',
            'user.update',
            'user.delete',
            'product.create',
            'product.view',
            'product.update',
            'product.delete',
            'customer.list',
            'customer.detail',
            'sale.list',
            'sale.detail',
            'sale.refund',
        ];
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $roles = [
            'ADMIN' => Permission::all(),
            'MANAGER' => Permission::whereIn('name', [
                'user.create',
                'user.view',
                'user.update',
                'user.delete',
                'product.create',
                'product.view',
                'product.update',
                'product.delete',
            ])->get(),
            'FINANCE' => Permission::whereIn('name', [
                'product.create',
                'product.view',
                'product.update',
                'product.delete',
                'sale.list',
                'sale.detail',
                'sale.refund',
            ])->get(),
            'USER' => Permission::whereIn('name', [
                'gateway.activate',
                'gateway.deactivate',
                'gateway.change-priority',
                'customer.list',
                'customer.detail',
            ])->get(),
        ];
        foreach ($roles as $roleName => $perms) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $role->syncPermissions($perms);
        }
        $users = [
            [
                'name' => 'Admin Master',
                'email' => 'admin@example.com',
                'role' => 'ADMIN',
            ],
            [
                'name' => 'Manager One',
                'email' => 'manager@example.com',
                'role' => 'MANAGER',
            ],
            [
                'name' => 'Finance Guy',
                'email' => 'finance@example.com',
                'role' => 'FINANCE',
            ],
            [
                'name' => 'Regular User',
                'email' => 'user@example.com',
                'role' => 'USER',
            ],
        ];
        foreach ($users as $data) {
            User::factory()->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'remember_token' => Str::random(10),
            ])->each(function ($user) use ($data) {
                $user->assignRole($data['role']);
            });
        }
        User::factory(10)->create()->each(function ($user) {
            $roles = Role::all()->pluck('name')->toArray();
            $user->assignRole(fake()->randomElement($roles));
        });
    }
}
