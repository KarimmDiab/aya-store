<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::create([
            'name' => 'karim',
            'email' => 'karim@admin.com',
            'gender' => 'male',
            'role' => 'super admin',
            'birth_date' => '1996-02-05',
            'status' => 'active',
            'password' => Hash::make('admin123'),
        ]);
        
        $user->assignRole('super admin');
        
    }
}
