<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@pump.test'],
            [
                'name' => 'Pump Admin',
                'phone' => '03001234567',
                'password' => Hash::make('password'),
                'role' => UserRole::Admin,
            ],
        );

        User::query()->updateOrCreate(
            ['email' => 'attendant@pump.test'],
            [
                'name' => 'Shift Attendant',
                'phone' => '03007654321',
                'password' => Hash::make('password'),
                'role' => UserRole::Attendant,
            ],
        );
    }
}
