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
        Schema::create('employee_allowances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id');
            $table->foreignId('allowance_type_id');
            $table->timestamps();

            $table->foreign('employee_id', 'fk_employee_allowances_employee')->references('id')->on('employees')->cascadeOnDelete();
            $table->foreign('allowance_type_id', 'fk_employee_allowances_type')->references('id')->on('allowance_types')->cascadeOnDelete();
            $table->unique(['employee_id', 'allowance_type_id'], 'uq_employee_allowance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_allowances');
    }
};
