<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name'     => 'Admin TCG',
                'email'    => 'admin@tcg.com',
                'password' => Hash::make('password'),
                'role'     => 'admin',
            ],
            [
                'name'     => 'Project Manager',
                'email'    => 'pm@tcg.com',
                'password' => Hash::make('password'),
                'role'     => 'pm',
            ],
            [
                'name'     => 'Engineer',
                'email'    => 'engineer@tcg.com',
                'password' => Hash::make('password'),
                'role'     => 'engineer',
            ],
            [
                'name'     => 'Viewer',
                'email'    => 'viewer@tcg.com',
                'password' => Hash::make('password'),
                'role'     => 'viewer',
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}
