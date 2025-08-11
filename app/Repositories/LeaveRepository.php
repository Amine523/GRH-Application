<?php

namespace App\Repositories;

use App\Mail\LeaveRequestMail;
use App\Models\Leave;
use DateTime;
use DateInterval;
use DatePeriod;
use Illuminate\Support\Facades\Mail;
use function Psy\debug;

class LeaveRepository
{
    /**
     * @param $data
     * @return void
     */
    public function createLeave($data, $startDay, $endDate, $startTime, $endTime = null): Leave
    {
        $leaveData = [
            'user_id' => $data->user_id,
            'type_of_leave' => $data->type_of_leave,
            'start_day' => $startDay->format('Y/m/d'),
            'start_time' => $startTime,
            'end_day' => $endDate->format('Y/m/d'),
            'status_of_leave' => 'pending',
        ];

        if ($data->type_of_leave === 'authorisation') {
            $leaveData['authorization_hour'] = (float)$data->authorization_hour;
            $leaveData['end_time'] = $endTime;
        }

        return Leave::create($leaveData);
    }

    /**
     * Calculate the number of weekdays between two dates.
     *
     * @param string $startDate
     * @param string $endDate
     * @return int
     */
    public function getWeekdaysBetween($startDate, $endDate)
    {
        $start = new DateTime($startDate);
        $end = new DateTime($endDate);

        // Include the end date in the range
        $end->modify('+1 day');

        $interval = new DateInterval('P1D'); // 1 day interval
        $dateRange = new DatePeriod($start, $interval, $end);

        $weekdays = 0;

        foreach ($dateRange as $date) {
            // Check if it's a weekday (1 = Monday, 7 = Sunday)
            if ($date->format('N') < 6) {
                $weekdays++;
            }
        }

        return $weekdays;
    }

    public function acceptVacation(Leave $leave)
    {
        if ($leave->type_of_leave === 'vacation') {
            $validBalance = $leave->user->valid_balance;
            $numberOfDays = $this->getWeekdaysBetween($leave->start_day, $leave->end_day);

            if ($validBalance >= $numberOfDays) {
                $leave->user->valid_balance -= $numberOfDays;
                $leave->status_of_leave = 'approved';
                $leave->user->save();
                $leave->save();

                Mail::to($leave->user->email)->send(new LeaveRequestMail($leave->user->profile->first_name, 'approved'));

            } else {
                Mail::to($leave->user->email)->send(new LeaveRequestMail($leave->user->profile->first_name, 'rejected'));
                return back()->with('error', 'Not enough vacation leave balance.');
            }
        }
        return back()->with('success', 'Vacation leave has been accepted.');
    }
}
