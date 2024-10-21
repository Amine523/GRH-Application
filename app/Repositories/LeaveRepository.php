<?php

namespace App\Repositories;

use App\Models\Leave;
use DateTime;
use DateInterval;
use DatePeriod;
use function Psy\debug;

class LeaveRepository
{
    /**
     * @param $data
     * @return void
     */
    public function createLeave($data, $startDay, $endDate)
    {
        Leave::create([
            'user_id' => $data->user_id,
            'type_of_leave' => $data->type_of_leave,
            'start_day' => $startDay->format('Y/m/d'),
            'end_day' => $endDate->format('Y/m/d'),
            'status_of_leave' => 'pending',
            'authorization_hour' => (float)$data->authorisationHours,
        ]);
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
}
