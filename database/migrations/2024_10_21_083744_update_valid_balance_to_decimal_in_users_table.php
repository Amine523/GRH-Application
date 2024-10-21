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
        Schema::table('users', function (Blueprint $table) {
            // Change valid_balance to decimal with precision 8 and scale 2 (e.g., 999999.99)
            $table->decimal('valid_balance', 8, 2)->default(23.00)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Change valid_balance back to integer if needed
            $table->integer('valid_balance')->default(23)->change();
        });
    }
};
