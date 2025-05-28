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
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->string('town')->nullable();
            $table->string('police_station')->nullable();
            $table->string('post_code')->nullable();
            $table->longText('area_details')->nullable();
            $table->string('address_type')->default('home')->comment('home, office, other');
            $table->string('apartment_address')->nullable();
            $table->timestamps();
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
