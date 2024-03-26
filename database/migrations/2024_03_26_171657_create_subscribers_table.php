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
        //         "name" => "John Doe"
        //   "email" => "john@gmail.com"
        //   "secondary_email" => null
        //   "mobile" => null
        //   "secondary_mobile" => null
        //   "telegram_username" => null
        //   "secondary_telegram_username" => null
        //   "tags" => array:2 [▼
        //     0 => "General"
        //     1 => "Test"
        //   ]
        Schema::create('subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('secondary_email')->nullable();
            $table->string('mobile')->nullable();
            $table->string('secondary_mobile')->nullable();
            $table->string('telegram_username')->nullable();
            $table->string('secondary_telegram_username')->nullable();
            $table->json('tags')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscribers');
    }
};
