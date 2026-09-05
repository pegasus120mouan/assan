<?php

use App\Http\Controllers\SeoController;
use App\Http\Controllers\Storefront\Account\AddressController;
use App\Http\Controllers\Storefront\Account\DashboardController as AccountDashboardController;
use App\Http\Controllers\Storefront\Account\OrderController as AccountOrderController;
use App\Http\Controllers\Storefront\Account\ProfileController as AccountProfileController;
use App\Http\Controllers\Storefront\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Storefront\Auth\NewPasswordController;
use App\Http\Controllers\Storefront\Auth\PasswordResetLinkController;
use App\Http\Controllers\Storefront\Auth\RegisteredUserController;
use App\Http\Controllers\Storefront\CartController;
use App\Http\Controllers\Storefront\CatalogController;
use App\Http\Controllers\Storefront\CheckoutController;
use App\Http\Controllers\Storefront\HomeController;
use App\Http\Controllers\Storefront\ProductController;
use App\Http\Controllers\Storefront\ReviewController;
use App\Http\Controllers\Storefront\SearchController;
use App\Http\Controllers\Storefront\WishlistController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');
Route::redirect('/login', '/connexion');

Route::get('/catalogue', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/categorie/{category:slug}', [CatalogController::class, 'category'])->name('catalog.category');
Route::get('/produit/{product:slug}', [ProductController::class, 'show'])->name('catalog.product');
Route::post('/produit/{product:slug}/avis', [ReviewController::class, 'store'])
    ->middleware(['auth', 'active'])
    ->name('reviews.store');
Route::get('/recherche', [SearchController::class, 'index'])->name('search');
Route::get('/recherche/suggestions', [SearchController::class, 'suggest'])->name('search.suggest');

Route::get('/panier', [CartController::class, 'index'])->name('cart.index');
Route::post('/panier', [CartController::class, 'add'])->name('cart.add');
Route::patch('/panier/{item}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/panier/{item}', [CartController::class, 'remove'])->name('cart.remove');
Route::delete('/panier', [CartController::class, 'clear'])->name('cart.clear');
Route::post('/panier/coupon', [CartController::class, 'applyCoupon'])->name('cart.coupon.apply');
Route::delete('/panier/coupon', [CartController::class, 'removeCoupon'])->name('cart.coupon.remove');

Route::get('/commande', [CheckoutController::class, 'show'])->name('checkout.show');
Route::post('/commande', [CheckoutController::class, 'store'])->middleware('throttle:8,1')->name('checkout.store');
Route::get('/commande/{order:order_number}/confirmation', [CheckoutController::class, 'confirmation'])->name('checkout.confirmation');

Route::middleware('guest')->group(function (): void {
    Route::get('/connexion', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/connexion', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:5,1');

    Route::get('/inscription', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/inscription', [RegisteredUserController::class, 'store'])->middleware('throttle:6,1');

    Route::get('/mot-de-passe-oublie', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/mot-de-passe-oublie', [PasswordResetLinkController::class, 'store'])->name('password.email');

    Route::get('/reinitialiser-mot-de-passe/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reinitialiser-mot-de-passe', [NewPasswordController::class, 'store'])->name('password.store');
});

Route::middleware(['auth', 'active'])->group(function (): void {
    Route::post('/deconnexion', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/compte', AccountDashboardController::class)->name('account.dashboard');
    Route::get('/compte/profil', [AccountProfileController::class, 'edit'])->name('account.profile.edit');
    Route::put('/compte/profil', [AccountProfileController::class, 'update'])->name('account.profile.update');
    Route::get('/compte/favoris', [WishlistController::class, 'index'])->name('account.wishlist');
    Route::post('/favoris/{product}', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
    Route::get('/compte/commandes', [AccountOrderController::class, 'index'])->name('account.orders.index');
    Route::get('/compte/commandes/{order:order_number}', [AccountOrderController::class, 'show'])->name('account.orders.show');
    Route::get('/compte/commandes/{order:order_number}/facture', [AccountOrderController::class, 'invoice'])->name('account.orders.invoice');
    Route::resource('compte/adresses', AddressController::class)
        ->parameters(['adresses' => 'address'])
        ->names('account.addresses')
        ->except(['show']);
});
