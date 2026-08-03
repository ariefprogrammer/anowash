<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('owners', function (Blueprint $table) {
            $table->uuid('referred_by_marketing_id')->nullable()->after('status');
            $table->foreign('referred_by_marketing_id', 'fk_owners_marketing')->references('id')->on('marketings')->nullOnDelete();
            $table->index('referred_by_marketing_id', 'idx_owners_marketing');
        });
    }

    public function down(): void
    {
        Schema::table('owners', function (Blueprint $table) {
            $table->dropForeign('fk_owners_marketing');
            $table->dropColumn('referred_by_marketing_id');
        });
    }
};
