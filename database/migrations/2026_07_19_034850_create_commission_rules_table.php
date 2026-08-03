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
        Schema::create('commission_rules', function (Blueprint $table) {
            $table->id();
            $table->uuid('owner_id');
            $table->foreignId('service_id')->nullable();
            $table->enum('basis', ['percentage', 'fixed'])->default('percentage');
            $table->decimal('value', 12, 2);
            $table->timestamps();

            $table->foreign('owner_id', 'fk_commission_rules_owner')->references('id')->on('owners')->cascadeOnDelete();
            $table->foreign('service_id', 'fk_commission_rules_service')->references('id')->on('services')->cascadeOnDelete();
            $table->unique(['owner_id', 'service_id'], 'uq_commission_rule');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commission_rules');
    }
};
