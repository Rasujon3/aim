<?php

namespace Database\Seeders;

use App\Modules\Roles\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::updateOrCreate(
            ['name' => 'Admin'],
            [
                'is_editable'   => false,
                'is_deletable'  => false,
                'created_by'    => 7,
                'updated_by'    => 7,
            ]
        );
    }
}
