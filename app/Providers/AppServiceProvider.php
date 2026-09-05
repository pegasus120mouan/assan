<?php

namespace App\Providers;

use App\Models\Order;
use App\Services\Payments\PaymentService;
use App\Services\SettingsService;
use App\Services\Storefront\CartService;
use App\Services\Storefront\PromotionService;
use App\Services\Storefront\WishlistService;
use App\Support\StorefrontCache;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Number;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PaymentService::class);
        $this->app->singleton(PromotionService::class);
        $this->app->singleton(SettingsService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $locale = (string) config('app.locale', 'fr');

        Carbon::setLocale($locale);
        Number::useLocale($locale);
        Number::useCurrency((string) config('shop.currency', 'XOF'));

        try {
            if (Schema::hasTable('settings')) {
                app(SettingsService::class)->applyToConfig();
            }
        } catch (\Throwable) {
            // Database not migrated yet (artisan / tests).
        }

        View::composer('layouts.storefront', function ($view): void {
            try {
                $cartSummary = app(CartService::class)->summary();

                $view->with('navCategories', StorefrontCache::navCategories());
                $view->with('cartCount', $cartSummary['count']);
                $view->with('cartTotal', $cartSummary['total']);
            } catch (\Throwable) {
                $view->with('navCategories', collect());
                $view->with('cartCount', 0);
                $view->with('cartTotal', 0);
            }
        });

        View::composer('layouts.admin', function ($view): void {
            $pending = 0;

            try {
                if (Schema::hasTable('orders')) {
                    $pending = Order::query()->pending()->count();
                }
            } catch (\Throwable) {
                $pending = 0;
            }

            $view->with('adminNavBadges', [
                'pending_orders' => $pending,
            ]);
        });

        View::composer('components.storefront.product-card', function ($view): void {
            try {
                $view->with('wishlistProductIds', app(WishlistService::class)->productIds(auth()->user()));
            } catch (\Throwable) {
                $view->with('wishlistProductIds', []);
            }
        });
    }
}
