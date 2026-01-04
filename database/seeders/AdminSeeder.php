<?php

namespace Database\Seeders;

use App\Domain\Admin\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        Admin::firstOrCreate(
            ['email' => 'admin@just-eat-clone.ch'],
            [
                'name'      => 'Super Admin',
                'password'  => Hash::make('password'),
                'role'      => 'super_admin',
                'is_active' => true,
            ]
        );

        Admin::firstOrCreate(
            ['email' => 'support@just-eat-clone.ch'],
            [
                'name'      => 'Support Agent',
                'password'  => Hash::make('password'),
                'role'      => 'support_agent',
                'is_active' => true,
            ]
        );
    }
}
