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
            'full_name'     => 'Super Admin',
            'email'         => 'superAdmin@gmail.com',
            'phone'         => '01712345678',
            'user_type_id'  => 1, // super_admin
            'role'          => 'super_admin',
            'ip_address'    => '127.0.0.1',
            'lat'           => '0',
            'long'          => '0',
            'day'           => now()->format('d'),
            'month'         => now()->format('M'),
            'year'          => now()->format('Y'),
            'password'      => Hash::make('123456'), // change in production
            'token'         => Str::random(60),
            'status'        => 'Active',
        ]);
    }
}
