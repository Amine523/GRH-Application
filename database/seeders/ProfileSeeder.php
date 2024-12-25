<?php

namespace Database\Seeders;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Profile::create([
            "user_id"=> 1,
            "first_name"=> 'admin',
            "last_name"=> ' ',
            "phone_number"=> '23735335',
            "profile_picture"=> '',
            "address"=> 'Route Ain klm 3 Ceinture Bourguiba',
        ]);
    }
}
