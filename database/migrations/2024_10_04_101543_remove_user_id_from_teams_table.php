<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            // Check if the column exists before trying to drop it
            if (Schema::hasColumn('teams', 'user_id')) {
                // Check if the foreign key exists before trying to drop it
                if (DB::getSchemaBuilder()->hasTable('teams')) {
                    $sm = Schema::getConnection()->getDoctrineSchemaManager();
                    $foreignKeys = $sm->listTableForeignKeys('teams');
                    
                    foreach ($foreignKeys as $foreignKey) {
                        if (in_array('user_id', $foreignKey->getLocalColumns())) {
                            $table->dropForeign([$foreignKey->getLocalColumns()[0]]);
                            break;
                        }
                    }
                }
                $table->dropColumn('user_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            if (!Schema::hasColumn('teams', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable();
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            }
        });
    }
};
