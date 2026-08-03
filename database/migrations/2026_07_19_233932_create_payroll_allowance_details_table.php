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
        Schema::create('payroll_allowance_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_record_id');
            $table->foreignId('allowance_type_id')->nullable();
            $table->string('name_snapshot', 100);
            $table->decimal('amount', 12, 2)->default(0);
            $table->timestamps();

            $table->foreign('payroll_record_id', 'fk_payroll_detail_record')->references('id')->on('payroll_records')->cascadeOnDelete();
            $table->foreign('allowance_type_id', 'fk_payroll_detail_type')->references('id')->on('allowance_types')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_allowance_details');
    }
};
