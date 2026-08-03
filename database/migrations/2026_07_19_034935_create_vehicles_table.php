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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->uuid('owner_id');
            $table->foreignId('customer_id')->nullable();
            $table->string('plate_number', 20);
            $table->foreignId('vehicle_category_id');
            $table->string('brand', 100)->nullable();
            $table->string('model', 100)->nullable();
            $table->string('color', 50)->nullable();
            $table->timestamps();

            $table->foreign('owner_id', 'fk_vehicles_owner')->references('id')->on('owners')->cascadeOnDelete();
            $table->foreign('customer_id', 'fk_vehicles_customer')->references('id')->on('customers')->nullOnDelete();
            $table->foreign('vehicle_category_id', 'fk_vehicles_category')->references('id')->on('vehicle_categories')->restrictOnDelete();
            $table->index(['owner_id', 'plate_number'], 'idx_vehicles_owner_plate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
