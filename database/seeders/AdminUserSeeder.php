<?php

namespace Database\Seeders;

use App\Enums\Status;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = (string) config('shop.admin.email');
        $password = config('shop.admin.password');

        if (blank($password)) {
            if (! app()->environment(['local', 'testing'])) {
                $this->command?->warn('ADMIN_PASSWORD est vide : le compte administrateur n’a pas été créé.');

                return;
            }

            $password = 'password';
        }

        $user = User::query()->firstOrNew(['email' => $email]);

        if ($user->exists) {
            return;
        }

        $user->forceFill([
            'name' => (string) config('shop.admin.name', 'Administrateur OVL'),
            'phone' => '0700000001',
            'password' => $password,
            'role' => UserRole::Admin,
            'status' => Status::Active,
        ])->save();
    }
}
