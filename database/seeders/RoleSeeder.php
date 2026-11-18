<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        collect(UserRole::cases())->each(
            fn (UserRole $role) => Role::findOrCreate($role->value),
        );
    }
}
