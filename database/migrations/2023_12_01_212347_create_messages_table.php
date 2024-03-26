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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->integer('messenger_id')->nullable();
            $table->string('type')->nullable()->comment('email, sms, push, etc');
            $table->string('status')->nullable()->comment('sent, failed, etc');
            $table->string('subject')->nullable();
            $table->string('body')->nullable();
            $table->string('from')->nullable();
            $table->string('to')->nullable();
            $table->integer('user_id')->nullable();
            $table->integer('subscriber_id')->nullable();
            $table->integer('campaign_id')->nullable();

            $table->integer('try_count')->default(0);
            $table->integer('max_try_count')->default(3);

            $table->timestamp('sent_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('clicked_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
