<?php

namespace App\Console\Commands;

use App\Enums\Status;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Command;

class ResetAdminCommand extends Command
{
    protected $signature = 'shop:reset-admin
                            {--email= : E-mail du compte admin}
                            {--password= : Nouveau mot de passe}
                            {--name= : Nom affiché}';

    protected $description = 'Crée ou réinitialise le compte administrateur (mot de passe, rôle, statut).';

    public function handle(): int
    {
        $email = (string) ($this->option('email') ?: config('shop.admin.email'));
        $password = (string) ($this->option('password') ?: config('shop.admin.password'));
        $name = (string) ($this->option('name') ?: config('shop.admin.name', 'Administrateur ASSAN'));

        if ($email === '' || $password === '') {
            $this->error('Indiquez --email et --password (ou ADMIN_EMAIL / ADMIN_PASSWORD dans le .env).');

            return self::FAILURE;
        }

        $user = User::query()->firstOrNew(['email' => $email]);
        $phone = $user->phone ?: '0700000001';

        if (! $user->exists && User::query()->where('phone', $phone)->exists()) {
            $phone = '07'.str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT);
        }

        $user->forceFill([
            'name' => $name,
            'phone' => $phone,
            'password' => $password,
            'role' => UserRole::Admin,
            'status' => Status::Active,
        ])->save();

        $this->info('Compte admin prêt : '.$user->email);

        return self::SUCCESS;
    }
}
