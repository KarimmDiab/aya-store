<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AccessRole;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function (): void {
            $role = AccessRole::query()
                ->where('name', 'Super admin')
                ->where('guard_name', 'web')
                ->firstOrFail();

            $user = User::firstOrCreate(
                ['email' => 'karim@admin.com'],
                [
                    'name' => 'karim',
                    'gender' => 'male',
                    'role' => $role->getKey(),
                    'birth_date' => '1996-02-05',
                    'status' => 'active',
                    'password' => Hash::make('admin123'),
                ]
            );

            if ($user->wasRecentlyCreated) {
                $user->assignRole($role);
            }
        });
    }
}
