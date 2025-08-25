<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('123'),

            ]
        );
        $this->command->newLine();
        $this->command->info('------------------------------------');
        $this->command->info('Default user created:');
        $this->command->info('Name: Test User');
        $this->command->info('Email: test@example.com');
        $this->command->info('Password: 123');
        $this->command->info('------------------------------------');
        $this->command->newLine();
    }
}
