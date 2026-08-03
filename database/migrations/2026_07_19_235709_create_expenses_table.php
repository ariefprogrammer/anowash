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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->uuid('outlet_id');
            $table->foreignId('expense_category_id')->nullable();
            $table->string('title', 150);
            $table->text('description')->nullable();
            $table->decimal('amount', 12, 2);
            $table->date('expense_date');
            $table->uuid('created_by')->nullable();
            $table->timestamps();

            $table->foreign('outlet_id', 'fk_expenses_outlet')->references('id')->on('outlets')->cascadeOnDelete();
            $table->foreign('expense_category_id', 'fk_expenses_category')->references('id')->on('expense_categories')->nullOnDelete();
            $table->foreign('created_by', 'fk_expenses_creator')->references('id')->on('users')->nullOnDelete();
            $table->index(['outlet_id', 'expense_date'], 'idx_expenses_outlet_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
