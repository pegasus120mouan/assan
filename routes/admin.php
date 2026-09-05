<?php

use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DeliveryController;
use App\Http\Controllers\Admin\DeliveryFeeController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\PromotionController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StockController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', DashboardController::class)->name('dashboard');

Route::resource('categories', CategoryController::class)->except(['show']);
Route::resource('marques', BrandController::class)
    ->parameters(['marques' => 'brand'])
    ->names('brands')
    ->except(['show']);

Route::get('produits/export', [ProductController::class, 'export'])->name('products.export');
Route::get('produits/import', [ProductController::class, 'import'])->name('products.import');
Route::post('produits/import', [ProductController::class, 'storeImport'])->name('products.import.store');
Route::post('produits/{product}/restore', [ProductController::class, 'restore'])
    ->name('products.restore')
    ->withTrashed();
Route::delete('produits/{product}/force', [ProductController::class, 'forceDestroy'])
    ->name('products.force-destroy')
    ->withTrashed();
Route::resource('produits', ProductController::class)
    ->parameters(['produits' => 'product'])
    ->names('products')
    ->except(['show']);

Route::get('/commandes', [OrderController::class, 'index'])->name('orders.index');
Route::get('/commandes/{order:order_number}', [OrderController::class, 'show'])->name('orders.show');
Route::patch('/commandes/{order:order_number}', [OrderController::class, 'update'])->name('orders.update');

Route::get('/paiements', [PaymentController::class, 'index'])->name('payments.index');
Route::get('/paiements/{payment}', [PaymentController::class, 'show'])->name('payments.show');
Route::patch('/paiements/{payment}', [PaymentController::class, 'update'])->name('payments.update');

Route::get('/livraisons', [DeliveryController::class, 'index'])->name('deliveries.index');
Route::get('/livraisons/{delivery}', [DeliveryController::class, 'show'])->name('deliveries.show');
Route::patch('/livraisons/{delivery}', [DeliveryController::class, 'update'])->name('deliveries.update');

Route::resource('frais-livraison', DeliveryFeeController::class)
    ->parameters(['frais-livraison' => 'delivery_fee'])
    ->names('delivery-fees')
    ->except(['show']);

Route::get('/stocks', [StockController::class, 'index'])->name('stock.index');
Route::get('/stocks/{product}', [StockController::class, 'show'])->name('stock.show');
Route::post('/stocks/{product}', [StockController::class, 'store'])->name('stock.store');
Route::get('/clients', [CustomerController::class, 'index'])->name('customers.index');
Route::get('/clients/{customer}', [CustomerController::class, 'show'])->name('customers.show');
Route::patch('/clients/{customer}', [CustomerController::class, 'update'])->name('customers.update');

Route::get('/avis', [ReviewController::class, 'index'])->name('reviews.index');
Route::patch('/avis/{review}', [ReviewController::class, 'update'])->name('reviews.update');

Route::resource('coupons', CouponController::class)->except(['show']);
Route::resource('promotions', PromotionController::class)->except(['show']);
Route::resource('utilisateurs', UserController::class)
    ->parameters(['utilisateurs' => 'user'])
    ->names('users')
    ->except(['show']);
Route::get('/parametres', [SettingController::class, 'edit'])->name('settings.index');
Route::put('/parametres', [SettingController::class, 'update'])->name('settings.update');
