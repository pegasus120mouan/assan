<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('session_id')->nullable()->index();
            $table->string('status', 32)->default('active')->index();
            $table->timestamp('last_activity_at')->nullable()->index();
            $table->timestamp('converted_at')->nullable();
            $table->timestamp('abandoned_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'last_activity_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
