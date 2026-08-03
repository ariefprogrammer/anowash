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
        Schema::create('outlet_services', function (Blueprint $table) {
            $table->id();
            $table->uuid('outlet_id');
            $table->foreignId('service_id');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('outlet_id', 'fk_outlet_services_outlet')->references('id')->on('outlets')->cascadeOnDelete();
            $table->foreign('service_id', 'fk_outlet_services_service')->references('id')->on('services')->cascadeOnDelete();
            $table->unique(['outlet_id', 'service_id'], 'uq_outlet_service');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outlet_services');
    }
};
