<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;

class TeamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::role('admin')->first();

        if ($user) {
            Team::create([
                "team_name" => "Softtodo",
                "project_manager_id" => $user->id,
                "profile_picture" => "",
            ]);
        } else {
            $this->command->info('No user with the "admin" role found. Please ensure an admin user exists.');
        }
    }
}
