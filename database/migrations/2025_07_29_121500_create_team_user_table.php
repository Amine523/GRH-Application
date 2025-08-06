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
        Schema::create('team_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('role')->default('member');
            $table->timestamps();
            
            // Empêcher les doublons
            $table->unique(['team_id', 'user_id']);
        });
        
        // Migrer les données existantes depuis employee_ids vers la table pivot
        $this->migrateExistingData();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('team_user');
    }
    
    /**
     * Migrate existing employee_ids to the new pivot table
     */
    private function migrateExistingData(): void
    {
        $teams = \App\Models\Team::all();
        
        foreach ($teams as $team) {
            if (!empty($team->employee_ids) && is_array($team->employee_ids)) {
                $syncData = [];
                
                foreach ($team->employee_ids as $userId) {
                    $syncData[$userId] = ['role' => 'member'];
                }
                
                // Ajouter le chef de projet s'il n'est pas déjà dans la liste
                if ($team->project_manager_id && !in_array($team->project_manager_id, $team->employee_ids)) {
                    $syncData[$team->project_manager_id] = ['role' => 'manager'];
                } elseif ($team->project_manager_id) {
                    // Mettre à jour le rôle du chef de projet s'il est déjà dans la liste
                    $syncData[$team->project_manager_id] = ['role' => 'manager'];
                }
                
                // Synchroniser les données dans la table pivot
                $team->employees()->sync($syncData);
            }
        }
    }
};
