<?php

namespace App\Services;

use App\Mail\LeaveRequestMail;
use Illuminate\Support\Facades\Mail;

class LeaveNotificationService
{
    public static function sendLeaveNotifications($user, $leave): void
    {
        Mail::to($user->email)->send(new LeaveRequestMail($user->fullName, 'user-approved', $leave));
        Mail::to(['grh@softtodo.com', 'fatma.abid@softtodo.com'])->send(new LeaveRequestMail($user->fullName, 'hr-notification', $leave));
//   $fullName = $user->fullName ?? 'Utilisateur';
        
//         // Send email to the user
//         Mail::to($user->email)->send(new LeaveRequestMail($fullName, 'user-approved', $leave));
        
//         // Send notification to HR
//         Mail::to(['grh@softtodo.com', 'fatma.abid@softtodo.com'])
//             ->send(new LeaveRequestMail($fullName, 'hr-notification', $leave));
    }
}
