<?php

namespace App\Services;

use App\Mail\LeaveRequestMail;
use Illuminate\Support\Facades\Mail;

class LeaveNotificationService
{
    public static function sendLeaveNotifications($user, $leave): void
    {
        // Send to user
        Mail::to($user->email)->send(new LeaveRequestMail($user->fullName, 'user-approved', $leave));
        
        // Find user's team and project manager
        $team = \App\Models\Team::whereJsonContains('employee_ids', $user->id)->first();
        if ($team && $team->projectManager) {
            // Send to project manager
            Mail::to($team->projectManager->email)
                ->send(new LeaveRequestMail($user->fullName, 'pm-notification', $leave));
        }
        
        // Send to HR
        Mail::to(['grh@softtodo.com', 'fatma.abid@softtodo.com'])
            ->send(new LeaveRequestMail($user->fullName, 'hr-notification', $leave));
        
//         // Send email to the user
//         Mail::to($user->email)->send(new LeaveRequestMail($fullName, 'user-approved', $leave));
        
//         // Send notification to HR
//         Mail::to(['grh@softtodo.com', 'fatma.abid@softtodo.com'])
//             ->send(new LeaveRequestMail($fullName, 'hr-notification', $leave));
    }
}
