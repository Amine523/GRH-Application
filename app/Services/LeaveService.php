<?php

namespace App\Services;

use Carbon\Carbon;

class LeaveService
{
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
