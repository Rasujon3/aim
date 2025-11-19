<?php

namespace Database\Seeders;

use App\Models\UserType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        UserType::create([
            'name' => 'Super Admin',
            'role' => 'super_admin',
            'project_id' => null,
            'is_showing' => false,
        ]);

        UserType::create([
            'name' => 'User',
            'role' => 'user',
            'project_id' => null,
            'is_showing' => true,
        ]);

        UserType::create([
            'name' => 'Hotel Owner',
            'role' => 'owner',
            'project_id' => 1,
            'is_showing' => false,
        ]);

        UserType::create([
            'name' => 'Hotel Receptionist',
            'role' => 'receptionist',
            'project_id' => 1,
            'is_showing' => true,
        ]);
    }
}
