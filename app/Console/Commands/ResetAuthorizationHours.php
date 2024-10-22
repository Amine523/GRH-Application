<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class ResetAuthorizationHours extends Command
{
    protected $signature = 'authorization:reset';
    protected $description = 'Reset authorization hours for users on the 1st of each month';

    public function handle()
    {
        User::query()->update(['authorization_hours' => 2.00]);
        $this->info('Authorization hours have been reset.');
    }
}
