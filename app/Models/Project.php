<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class Project extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'manager_id',
        'member_ids',
        'start_date',
        'end_date',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'member_ids' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Get the manager of the project.
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Get all members of the project.
     */
    public function members()
    {
        return User::whereIn('id', $this->member_ids ?? [])->get();
    }

    /**
     * Get all team members including the manager.
     */
    public function getAllMembersAttribute()
    {
        $members = $this->members();
        if (!$members->contains('id', $this->manager_id)) {
            $members->push($this->manager);
        }
        return $members;
    }

    /**
     * Check if a user is a member of the project.
     */
    public function isMember(User $user): bool
    {
        return $this->manager_id === $user->id || 
               in_array($user->id, $this->member_ids ?? []);
    }

    /**
     * Scope a query to only include projects for a specific user.
     */
    public function scopeForUser($query, User $user)
    {
        return $query->where('manager_id', $user->id)
            ->orWhereJsonContains('member_ids', $user->id);
    }

    /**
     * Scope a query to only include active projects.
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['planning', 'in_progress']);
    }

    /**
     * Get the project's status with a human-readable label.
     */
    public function getStatusLabelAttribute(): string
    {
        return [
            'planning' => 'Planning',
            'in_progress' => 'En cours',
            'on_hold' => 'En attente',
            'completed' => 'Terminé',
            'cancelled' => 'Annulé',
        ][$this->status] ?? 'Inconnu';
    }

    /**
     * Get the project's duration in days.
     */
    public function getDurationInDaysAttribute(): ?int
    {
        if ($this->start_date && $this->end_date) {
            return $this->start_date->diffInDays($this->end_date);
        }
        return null;
    }

    /**
     * Get the project's working days.
     */
    public function countWorkingDays(): int
    {
        $startDate = $this->start_date;
        $endDate = $this->end_date;

        $workingDays = 0;

        while ($startDate <= $endDate) {
            if (!in_array($startDate->dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY])) {
                $workingDays++;
            }
            $startDate->addDay();
        }

        return $workingDays;
    }

    /**
     * Vérifie s'il y a un conflit de congés entre les membres du projet pour une période donnée
     *
     * @param int $userId ID de l'utilisateur qui demande le congé
     * @param string $startDate Date de début du congé (format Y-m-d)
     * @param string $endDate Date de fin du congé (format Y-m-d)
     * @return array Retourne un tableau avec 'has_conflict' (bool) et 'conflicting_users' (array)
     */
    public function checkLeaveConflict(int $userId, string $startDate, string $endDate): array
    {
        $memberIds = $this->member_ids ?? [];
        
        // Exclure l'utilisateur actuel de la vérification
        $memberIds = array_diff($memberIds, [$userId]);
        
        if (empty($memberIds)) {
            return [
                'has_conflict' => false,
                'conflicting_users' => []
            ];
        }

        $startDate = Carbon::parse($startDate);
        $endDate = Carbon::parse($endDate);

        // Récupérer les congés des autres membres du projet qui chevauchent la période demandée
        $conflictingLeaves = Leave::whereIn('user_id', $memberIds)
            ->where(function($query) use ($startDate, $endDate) {
                $query->whereBetween('start_day', [$startDate, $endDate])
                    ->orWhereBetween('end_day', [$startDate, $endDate])
                    ->orWhere(function($q) use ($startDate, $endDate) {
                        $q->where('start_day', '<=', $startDate)
                            ->where('end_day', '>=', $endDate);
                    });
            })
            ->where('status_of_leave', 'approved')
            ->with('user.profile')
            ->get();

        if ($conflictingLeaves->isEmpty()) {
            return [
                'has_conflict' => false,
                'conflicting_users' => []
            ];
        }

        // Formater les informations sur les conflits
        $conflictingUsers = [];
        foreach ($conflictingLeaves as $leave) {
            $conflictingUsers[] = [
                'id' => $leave->user->id,
                'name' => $leave->user->profile->first_name . ' ' . $leave->user->profile->last_name,
                'start_date' => $leave->start_day->format('d/m/Y'),
                'end_date' => $leave->end_day->format('d/m/Y'),
                'type' => $leave->type_of_leave
            ];
        }

        return [
            'has_conflict' => true,
            'conflicting_users' => $conflictingUsers
        ];
    }

    /**
     * Check if the project is active.
     */
    public function isActive(): bool
    {
        return in_array($this->status, ['planning', 'in_progress']);
    }

    /**
     * Get the project's progress percentage.
     */
    public function getProgressPercentage(): int
    {
        // À implémenter selon les besoins spécifiques
        return 0;
    }
}
