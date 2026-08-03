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
        Schema::create('service_prices', function (Blueprint $table) {
            $table->id();
            $table->uuid('outlet_id');
            $table->foreignId('service_id');
            $table->foreignId('vehicle_category_id');
            $table->decimal('price', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('outlet_id', 'fk_service_prices_outlet')->references('id')->on('outlets')->cascadeOnDelete();
            $table->foreign('service_id', 'fk_service_prices_service')->references('id')->on('services')->cascadeOnDelete();
            $table->foreign('vehicle_category_id', 'fk_service_prices_category')->references('id')->on('vehicle_categories')->cascadeOnDelete();
            $table->unique(['outlet_id', 'service_id', 'vehicle_category_id'], 'uq_service_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_prices');
    }
};
