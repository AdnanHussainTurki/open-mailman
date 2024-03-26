<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('subscribers');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('secondary_email')->nullable();
            $table->string('mobile')->nullable();
            $table->string('secondary_mobile')->nullable();
            $table->string('telegram_username')->nullable();
            $table->string('secondary_telegram_username')->nullable();
            $table->timestamps();
        });
    }
};
