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
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();

            $table->foreignId("user_id")
                ->constrained()
                ->cascadeOnDelete();

            $table->string("label")->nullable(); // Home , Work, etc.
            $table->string("first_name");
            $table->string("last_name");
            $table->string("company")->nullable();

            $table->string("address_line1");
            $table->string("address_line2")->nullable();
            $table->string("city")->nullable();
            $table->string("state")->nullable();
            $table->string("postal_code")->nullable();
            $table->string("country_code", 2)->nullable(); //iso country code

            $table->string('phone')->nullable();

            $table->boolean('is_default_shipping')->default(false);
            $table->boolean('is_default_billing')->default(false);

            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();


            $table->timestamps();
            $table->softDeletes();

            $table->index(["user_id"]);
            $table->index(["is_default_shipping", "is_default_billing"]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
