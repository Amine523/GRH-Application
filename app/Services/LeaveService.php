<?php

namespace App\Services;

use App\Mail\LeaveRequestMail;
use App\Models\Leave;
use Carbon\Carbon;
use Exception;
use JetBrains\PhpStorm\NoReturn;
use Mail;

class LeaveService
{
    /**
     * @throws Exception
     */
    public function approve(int $leaveId): void
    {
        $leave = Leave::with('user')->findOrFail($leaveId);
        if ($leave->status_of_leave === 'approved') {
            return;
        }
        $numberOfDays = $this->countWorkingDays($leave->start_day, $leave->end_day);

        $daysToDeduct = match ($leave->type_of_leave) {
            'halfday' => 0.5,
            'authorisation' => 0,
            default => $numberOfDays,
        };

        if ($leave->type_of_leave === 'authorisation') {
            $this->handleAuthorisationLeave($leave);
        } else {
            $this->handleStandardLeave($leave, $daysToDeduct);
        }
    }

    /**
     * @throws Exception
     */
    private function handleAuthorisationLeave(Leave $leave): void
    {
        $user = $leave->user;

        $monthlyAuthorizationCount = Leave::where('user_id', $user->id)
            ->where('type_of_leave', 'authorisation')
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();

        if ($monthlyAuthorizationCount === 0) {
            $user->authorization_hours = 2;
        }

        $user->authorization_hours -= $leave->authorization_hour;

        if ($user->authorization_hours <= -4) {
            $user->valid_balance -= 0.5;
            $user->authorization_hours += 4;
        }

        $leave->status_of_leave = 'approved';
        $user->save();
        $leave->save();

        Mail::to($user->email)->send(new LeaveRequestMail($user->profile->first_name, 'approved-authorisation'));
    }

    /**
     * @throws Exception
     */
    private function handleStandardLeave(Leave $leave, $daysToDeduct): void
    {
        if ($leave->user->valid_balance >= $daysToDeduct) {
            $leave->user->valid_balance -= $daysToDeduct;
            $leave->status_of_leave = 'approved';
        } else {
            throw new Exception('Not enough valid balance available.');
        }

        $leave->user->save();
        $leave->save();
    }

    public function countWorkingDays($startDate, $endDate): int
    {
        if (!$startDate || !$endDate) {
            return 0;
        }

        $startDate = Carbon::parse($startDate);
        $endDate = Carbon::parse($endDate);

        $workingDays = 0;
        while ($startDate <= $endDate) {
            if (!in_array($startDate->dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY])) {
                $workingDays++;
            }
            $startDate->addDay();
        }

        return $workingDays;
    }
}
