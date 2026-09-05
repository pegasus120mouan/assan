<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('type', 32)->index();
            $table->unsignedInteger('value');
            $table->unsignedInteger('minimum_order_amount')->default(0);
            $table->unsignedInteger('maximum_discount')->nullable();
            $table->timestamp('starts_at')->nullable()->index();
            $table->timestamp('expires_at')->nullable()->index();
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('usage_per_customer')->nullable();
            $table->unsignedInteger('used_count')->default(0);
            $table->string('status', 32)->default('active')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
