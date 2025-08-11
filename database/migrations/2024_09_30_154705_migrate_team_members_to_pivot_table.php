<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Team;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $teams = DB::table('teams')->get();

        foreach ($teams as $team) {
            if (!empty($team->employee_ids)) {
                $employeeIds = json_decode($team->employee_ids, true);
                if (is_array($employeeIds)) {
                    foreach ($employeeIds as $userId) {
                        DB::table('team_user')->insert([
                            'team_id' => $team->id,
                            'user_id' => $userId,
                            'is_project_manager' => $userId == $team->project_manager_id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            // Also ensure project manager is added as a member if not already
            if (!empty($team->project_manager_id)) {
                DB::table('team_user')->updateOrInsert(
                    [
                        'team_id' => $team->id,
                        'user_id' => $team->project_manager_id,
                    ],
                    [
                        'is_project_manager' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        // Remove the employee_ids column
        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn('employee_ids');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add back the employee_ids column
        Schema::table('teams', function (Blueprint $table) {
            $table->json('employee_ids')->nullable();
        });

        // Restore employee_ids data from pivot table
        $teams = DB::table('teams')->get();
        foreach ($teams as $team) {
            $employeeIds = DB::table('team_user')
                ->where('team_id', $team->id)
                ->pluck('user_id')
                ->toArray();

            DB::table('teams')
                ->where('id', $team->id)
                ->update([
                    'employee_ids' => json_encode($employeeIds)
                ]);
        }
    }
};
