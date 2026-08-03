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
        Schema::create('order_item_workers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id');
            $table->foreignId('employee_id');
            $table->enum('split_type', ['equal', 'percentage', 'fixed_amount'])->default('equal');
            $table->decimal('split_value', 12, 2)->nullable();
            $table->decimal('commission_amount', 12, 2)->default(0);
            $table->timestamps();

            $table->foreign('order_item_id', 'fk_oiw_order_item')->references('id')->on('order_items')->cascadeOnDelete();
            $table->foreign('employee_id', 'fk_oiw_employee')->references('id')->on('employees')->cascadeOnDelete();
            $table->unique(['order_item_id', 'employee_id'], 'uq_order_item_employee');
            $table->index('employee_id', 'idx_oiw_employee');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_item_workers');
    }
};
