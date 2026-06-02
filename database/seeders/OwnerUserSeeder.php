<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class OwnerUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'saheedabdulganiyu01@gmail.com'],
            [
                'uuid' => Str::uuid()->toString(),
                'institution_id' => null,
                'username' => 'owner',
                'first_name' => 'Saheed',
                'last_name' => 'Abdulganiyu',
                'password' => Hash::make('@Saheed01'),
                'role' => 'super_admin',
                'email_verified_at' => now(),
            ]
        );
    }
}
