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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->uuid('owner_id');
            $table->uuid('outlet_id');
            $table->string('name', 150);
            $table->string('phone', 30)->nullable();
            $table->text('address')->nullable();
            $table->date('join_date')->nullable();
            $table->enum('payroll_type', ['flat', 'commission'])->default('commission');
            $table->decimal('flat_salary', 12, 2)->nullable();
            $table->string('photo_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('owner_id', 'fk_employees_owner')->references('id')->on('owners')->cascadeOnDelete();
            $table->foreign('outlet_id', 'fk_employees_outlet')->references('id')->on('outlets')->cascadeOnDelete();
            $table->index(['owner_id', 'outlet_id'], 'idx_employees_owner_outlet');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
