<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class GuruSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default guru/admin users
        User::create([
            'name' => 'Admin OSIS',
            'username' => 'admin',
            'password' => Hash::make('password'),
            'active' => true,
        ]);

        User::create([
            'name' => 'Guru Pembina',
            'username' => 'guru1',
            'password' => Hash::make('password'),
            'active' => true,
        ]);

        User::create([
            'name' => 'Guru Wali Kelas',
            'username' => 'guru2',
            'password' => Hash::make('password'),
            'active' => true,
        ]);
    }
}
