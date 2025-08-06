<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
class Leave extends Model
{
    use SoftDeletes;

    // The attributes that are mass assignable.
    protected $fillable = [
        'user_id',
        'start_day',
        'start_time',
        'end_day',
        'type_of_leave',
        'status_of_leave',
        'authorization_hour',
        'deduction_days'
    ];

    // Relation to the User model (one leave belongs to one user)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function countWorkingDays(): int
    {
        $startDate = $this->start_date;
        $endDate = $this->end_date;

        $workingDays = 0;

        while ($startDate <= $endDate) {
            if (!in_array($startDate->dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY, Carbon::Holiday])) {
                $workingDays++;
            }
            $startDate->addDay();
        }

        return $workingDays;
    }

    protected function casts(): array
    {
        return [
            'start_day' => 'datetime:d/m/Y',
            'end_day' => 'datetime:d/m/Y',
        ];
    }

}
