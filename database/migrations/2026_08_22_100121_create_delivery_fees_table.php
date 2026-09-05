<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('commune_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('zone_id')->nullable()->constrained()->nullOnDelete();
            $table->string('delivery_method', 64)->default('standard')->index();
            $table->unsignedInteger('min_order_amount')->default(0);
            $table->unsignedInteger('max_order_amount')->nullable();
            $table->decimal('min_weight', 8, 2)->nullable();
            $table->decimal('max_weight', 8, 2)->nullable();
            $table->unsignedInteger('fee');
            $table->unsignedInteger('free_above_amount')->nullable();
            $table->string('status', 32)->default('active')->index();
            $table->timestamps();

            $table->index(['delivery_method', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_fees');
    }
};
