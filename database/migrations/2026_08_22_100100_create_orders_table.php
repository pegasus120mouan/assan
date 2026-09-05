<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('coupon_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 32)->default('pending')->index();
            $table->string('payment_status', 32)->default('pending')->index();
            $table->string('delivery_status', 32)->default('pending')->index();
            $table->unsignedInteger('subtotal')->default(0);
            $table->unsignedInteger('discount_amount')->default(0);
            $table->unsignedInteger('delivery_fee')->default(0);
            $table->unsignedInteger('total')->default(0);
            $table->string('payment_method', 64)->nullable();
            $table->string('delivery_method', 64)->nullable();
            $table->string('customer_name');
            $table->string('customer_phone', 30);
            $table->string('customer_email')->nullable();
            $table->string('delivery_address');
            $table->foreignId('delivery_commune_id')->nullable()->constrained('communes')->nullOnDelete();
            $table->string('delivery_commune')->nullable();
            $table->string('delivery_city')->nullable();
            $table->text('customer_notes')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index('created_at');
            $table->index(['status', 'created_at']);
            $table->index('customer_phone');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
