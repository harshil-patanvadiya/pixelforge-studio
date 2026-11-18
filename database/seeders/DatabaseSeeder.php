<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use App\Services\WalletService;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    private const PASSWORD = '1234567890';

    /**
     * Seed the application's database.
     */
    public function run(WalletService $walletService): void
    {
        $this->call(RoleSeeder::class);

        $admin = User::factory()
            ->create(['name' => 'Admin', 'email' => 'admin@yopmail.com', 'password' => self::PASSWORD]);
        $admin->assignRole(UserRole::Admin->value);
        $walletService->grantStartingCredits($admin);

        $users = [
            ['name' => 'John', 'email' => 'john@yopmail.com'],
            ['name' => 'Alice', 'email' => 'alice@yopmail.com'],
            ['name' => 'Bob', 'email' => 'bob@yopmail.com'],
            ['name' => 'Emma', 'email' => 'emma@yopmail.com'],
            ['name' => 'David', 'email' => 'david@yopmail.com'],
        ];

        collect($users)->each(
            function (array $data) use ($walletService): void {
                $user = User::factory()->create([...$data, 'password' => self::PASSWORD]);
                $user->assignRole(UserRole::User->value);
                $walletService->grantStartingCredits($user);
            },
        );
    }
}