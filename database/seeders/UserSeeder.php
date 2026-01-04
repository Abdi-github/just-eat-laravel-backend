<?php

namespace Database\Seeders;

use App\Domain\User\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users     = json_decode(file_get_contents(database_path('../data/users.json')), true);
        $userRoles = json_decode(file_get_contents(database_path('../data/user_roles.json')), true);
        $roles     = json_decode(file_get_contents(database_path('../data/roles.json')), true);

        // Build role _id → name map
        $roleNameMap = [];
        foreach ($roles as $r) {
            $roleNameMap[$r['_id']] = $r['name'];
        }

        // Build user _id → role name map
        $userRoleMap = [];
        foreach ($userRoles as $ur) {
            $userRoleMap[$ur['user_id']] = $roleNameMap[$ur['role_id']] ?? 'customer';
        }

        $usedUsernames = [];

        // Build _id → User model map for other seeders to use if needed
        foreach ($users as $item) {
            $baseUsername = isset($item['username'])
                ? $item['username']
                : strtolower(($item['first_name'] ?? '') . '.' . ($item['last_name'] ?? ''));

            $username = $baseUsername;
            $suffix = 1;
            while (in_array($username, $usedUsernames)) {
                $username = $baseUsername . $suffix++;
            }
            $usedUsernames[] = $username;

            $email = $item['email'];
            $existing = User::where('email', $email)->first();

            if (! $existing) {
                // Use DB::table to bypass the 'hashed' password cast — password_hash is already a bcrypt hash
                DB::table('users')->insert([
                    'username'            => $username,
                    'email'               => $email,
                    'password'            => str_replace('$2b$', '$2y$', $item['password_hash']),
                    'first_name'          => $item['first_name'] ?? null,
                    'last_name'           => $item['last_name'] ?? null,
                    'phone'               => $item['phone'] ?? null,
                    'avatar'              => $item['avatar_url'] ?? null,
                    'is_active'           => $item['is_active'] ?? true,
                    'preferred_language'  => in_array($item['preferred_language'] ?? 'fr', ['fr', 'de', 'en']) ? $item['preferred_language'] : 'fr',
                    'email_verified_at'   => isset($item['verified_at']) ? now() : null,
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ]);
            }

            $user = User::where('email', $email)->first();

            // Assign role
            $roleName = $userRoleMap[$item['_id']] ?? 'customer';
            if (! $user->hasRole($roleName)) {
                $user->assignRole($roleName);
            }
        }
    }
}
