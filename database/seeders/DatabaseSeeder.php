<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Compte administrateur + contenu initial du site.
     * Identifiants : variables ADMIN_EMAIL / ADMIN_PASSWORD du fichier .env.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@elc.test')],
            [
                'name' => env('ADMIN_NAME', 'Administrateur ELC'),
                'password' => env('ADMIN_PASSWORD', 'ChangeMoi-2026'),
                'role' => 'admin',
                'is_active' => true,
                'email_verified_at' => now(),
            ],
        );

        $this->call(ContentSeeder::class);
    }
}
