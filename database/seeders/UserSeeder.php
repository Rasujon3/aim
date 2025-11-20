<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'full_name'      => 'Super Administrator',
            'email'          => 'super.admin@gmail.com',
            'role_id'        => null,
            'role'           => 'super_admin',
            'ip_address'     => '127.0.0.1',
            'is_view_all'    => '1',
            'is_create_all'  => '1',
            'is_edit_all'    => '1',
            'password'       => '123456',
            'token'          => null,
            'email_verified_at' => now(),
            'status'         => 'Active',
            'created_by'     => null,
            'updated_by'     => null,
        ]);
    }
}
