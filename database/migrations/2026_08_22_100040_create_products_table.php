<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sku')->unique();
            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();
            $table->unsignedInteger('purchase_price')->default(0);
            $table->unsignedInteger('selling_price');
            $table->unsignedInteger('compare_price')->nullable();
            $table->unsignedInteger('cost_price')->nullable();
            $table->unsignedInteger('stock_quantity')->default(0);
            $table->unsignedInteger('reserved_quantity')->default(0);
            $table->unsignedInteger('low_stock_threshold')->default(5);
            $table->decimal('weight', 8, 2)->nullable();
            $table->string('status', 32)->default('active')->index();
            $table->boolean('featured')->default(false)->index();
            $table->boolean('is_new')->default(false)->index();
            $table->boolean('is_best_seller')->default(false)->index();
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 500)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('created_at');
            $table->index(['status', 'featured']);
            $table->index(['status', 'is_new']);
            $table->index(['status', 'is_best_seller']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
