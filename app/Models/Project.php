<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
     * The users that belong to the project.
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_user');
    }

    /**
     * Get all team members including the manager.
     */
    public function getAllMembersAttribute()
    {
        return $this->members->merge([$this->manager])->unique('id');
    }

    /**
     * Get all members of the project.
     */
    public function users()
    {
        $memberIds = $this->member_ids ?? [];
        return User::whereIn('id', $memberIds)
            ->orWhere('id', $this->manager_id);
    }

    /**
     * Check if a user is a member of the project.
     */
    public function isMember(User $user): bool
    {
        return $this->manager_id === $user->id || 
               $this->users->contains($user->id);
    }

    /**
     * Scope a query to only include projects for a specific user.
     */
    public function scopeForUser($query, User $user)
    {
        return $query->where('manager_id', $user->id)
            ->orWhereHas('users', function ($query) use ($user) {
                $query->where('id', $user->id);
            });
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
     * Get the leaves associated with the project.
     */
    // public function leaves(): HasMany
    // {
    //     return $this->hasMany(Leave::class);
    // }
}