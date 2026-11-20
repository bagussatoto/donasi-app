<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Kredensial admin dapat diatur lewat ADMIN_EMAIL dan ADMIN_PASSWORD di .env.
     */
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@example.com');

        if (User::where('email', $email)->exists()) {
            return;
        }

        User::create([
            'user_id' => (string) Str::uuid(),
            'name' => 'Admin',
            'email' => $email,
            'password' => bcrypt(env('ADMIN_PASSWORD', 'ChangeMe123!')),
            'role' => 'admin',
        ]);
    }
}
