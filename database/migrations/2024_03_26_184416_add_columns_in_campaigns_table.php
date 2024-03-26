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
        Schema::table('campaigns', function (Blueprint $table) {
            // slug
            $table->string('slug')->unique();
            // rate_limiting_in_seconds
            $table->integer('rate_limiting_in_seconds')->default(0);
            // content
            $table->longText('content')->nullable();
            // scheduled_at
            $table->timestamp('scheduled_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropColumn('slug');
            $table->dropColumn('rate_limiting_in_seconds');
            $table->dropColumn('content');
            $table->dropColumn('scheduled_at');
        });
    }
};
