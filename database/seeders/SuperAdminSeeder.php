<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    /**
     * Usuario super administrador (idempotente por correo).
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'tecnologia@colbeef.com'],
            [
                'name' => 'Juan Carreño',
                'username' => 'juan.carreno',
                'password' => 'SIRT123',
                'role' => 'super_admin',
                'email_verified_at' => now(),
            ],
        );
    }
}
