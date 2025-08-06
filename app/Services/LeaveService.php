<?php

namespace App\Services;

use App\Http\Requests\LeaveRequest;
use App\Mail\LeaveRequestMail;
use App\Models\Leave;
use App\Models\User;
use App\Models\Holiday;
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

        $monthlyAuthorizationHours = Leave::where('user_id', $user->id)
            ->where('type_of_leave', 'authorisation')
            ->where('status_of_leave', 'approved')
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('authorization_hour');

        if ($monthlyAuthorizationHours === 0) {
            $user->authorization_hours = 0;
        }

        $totalHoursWithCurrentLeave = $user->authorization_hours + $leave->authorization_hour;

        if ($totalHoursWithCurrentLeave > 6) {
            throw new Exception('User has already reached the monthly limit of 6 authorization hours.');
            foreach ($admins as $admin) {
                $userName=$user->profile ? "{$user->profie->first_name} {$user->profile->last_name}" :$user->email;
                $message= "l'utilisation {$userName}  a depassé la durée maximale d'autorisation pour ce mois. Total des heures : {$totalHoursWithCurrentLeave}h. ";
                Mail::raw ($message,function ($mail) use ($admin){
                    $mail->to($admin->email)->subject ('Alerte : Dépassement d\'heures d\'autorisation');

                });
            }
        }

        if ($totalHoursWithCurrentLeave == 6) {
            $user->valid_balance -= 0.5;
        }

        $user->authorization_hours = $totalHoursWithCurrentLeave;

        $leave->status_of_leave = 'approved';
        $user->save();
        $leave->save();
    }

    /**
     * @throws Exception
     */
    private function handleStandardLeave(Leave $leave, $daysToDeduct): void
    {
        $leave->user->valid_balance -= $daysToDeduct;
        $leave->status_of_leave = 'approved';

        $leave->user->save();
        $leave->save();
    }

    /**
     * Liste des jours fériés fixes en Tunisie (format: 'm-d')
     * @var array
     */
    private $tunisianHolidays = [
        '01-01', // Nouvel An
        '03-20', // Fête de l'Indépendance
        '04-09', // Fête des Martyrs
        '05-01', // Fête du Travail
        '07-25', // Fête de la République
        '08-13', // Fête de la Femme et de la Famille
        '10-15', // Fête de l'Évacuation
        '12-17', // Fête de la Révolution
    ];

    /**
     * Calcule le nombre de jours ouvrés entre deux dates
     * Ne compte pas les week-ends ni les jours fériés tunisiens
     *
     * @param string $startDate Date de début
     * @param string $endDate Date de fin
     * @return int Nombre de jours ouvrés
     */
    public function countWorkingDays($startDate, $endDate): int
    {
        if (!$startDate || !$endDate) {
            return 0;
        }

        $startDate = Carbon::parse($startDate);
        $endDate = Carbon::parse($endDate);
        $workingDays = 0;
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            // Vérifier si c'est un week-end
            $isWeekend = in_array($currentDate->dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY]);
            
            // Vérifier si c'est un jour férié (fixe)
            $isHoliday = in_array($currentDate->format('m-d'), $this->tunisianHolidays);

            // Incrémenter uniquement pour les jours ouvrés (ni week-end, ni férié)
            if (!$isWeekend && !$isHoliday) {
                $workingDays++;
            }

            $currentDate->addDay();
        }

        return $workingDays;
    }

    public function handleLateDeduction(User $user, LeaveRequest $leaveRequest): void
    {
        $deductionDays = $leaveRequest->deduction_days;

        $today = Carbon::today();

        Leave::create([
            'user_id' => $user->id,
            'type_of_leave' => 'deduction',
            'start_day' => $today->format('Y/m/d'),
            'start_time' => now()->format('H:i'),
            'end_day' => $today->format('Y/m/d'),
            'status_of_leave' => 'approved',
            'authorization_hour' => 0,
            'deduction_days' => $deductionDays,
        ]);

        $user->valid_balance -= $deductionDays;
        $user->save();
    }
}
