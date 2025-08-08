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
        Schema::create('user_logins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('ip_address', 45)->nullable(); // supports IPv6
            $table->string('user_agent')->nullable();
            $table->string('device')->nullable(); // optional: parsed device name
            $table->string('platform')->nullable(); // Windows, Mac, iOS, etc.
            $table->string('browser')->nullable(); // Chrome, Firefox, etc.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_logins');
    }
};
