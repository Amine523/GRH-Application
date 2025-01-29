<?php

namespace App\Services;

use App\Mail\LeaveRequestMail;
use Illuminate\Support\Facades\Mail;

class LeaveNotificationService
{
    public static function sendLeaveNotifications($user, $leave): void
    {
        Mail::to($user->email)->send(new LeaveRequestMail($user->fullName, 'user-approved', $leave));
        Mail::to('hr@company.com')->send(new LeaveRequestMail($user->fullName, 'hr-notification', $leave));
    }
}
