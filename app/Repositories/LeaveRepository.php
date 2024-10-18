<?php

namespace App\Repositories;

use DateTime;
use DateInterval;
use DatePeriod;

class LeaveRepository
{
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
