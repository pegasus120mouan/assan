<?php

namespace App\Console\Commands;

use App\Enums\CartStatus;
use App\Models\Cart;
use Illuminate\Console\Command;

class MarkAbandonedCartsCommand extends Command
{
    protected $signature = 'carts:mark-abandoned {--hours= : Inactivité en heures}';

    protected $description = 'Marque les paniers inactifs comme abandonnés (sans envoyer d’e-mail ni de WhatsApp).';

    public function handle(): int
    {
        $hours = (int) ($this->option('hours') ?: config('shop.abandoned_cart_hours', 48));

        $updated = Cart::query()
            ->active()
            ->where('last_activity_at', '<', now()->subHours($hours))
            ->update([
                'status' => CartStatus::Abandoned,
                'abandoned_at' => now(),
            ]);

        $this->info($updated.' panier(s) marqué(s) abandonné(s).');

        return self::SUCCESS;
    }
}
