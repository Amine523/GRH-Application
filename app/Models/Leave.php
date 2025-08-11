<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Leave extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'start_day',
        'start_time',
        'end_time',
        'end_day',
        'type_of_leave',
        'status_of_leave',
        'authorization_hour',
        'deduction_days'
    ];

    protected $appends = ['is_overlapping', 'overlap_message'];

    protected $casts = [
        'start_day' => 'date:d/m/Y',
        'end_day' => 'date:d/m/Y',
        'deleted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'authorization_hour' => 'float',
        'deduction_days' => 'float'
    ];
    
    protected $dateFormat = 'Y-m-d H:i:s';
    
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('d/m/Y');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get a message explaining why the leave request overlaps
     */
    public function getOverlapMessageAttribute(): ?string
    {
        if (!$this->is_overlapping) {
            return null;
        }

        $user = User::find($this->user_id);
        if (!$user) {
            return null;
        }

        // Check project overlaps first (prioritize project conflicts)
        $projectOverlaps = [];
        $projects = Project::where('status', 'active')
            ->where(function($query) use ($user) {
                $query->where('manager_id', $user->id)
                    ->orWhereJsonContains('member_ids', $user->id);
            })
            ->get();

        foreach ($projects as $project) {
            $projectMembers = array_merge(
                [$project->manager_id],
                $project->member_ids ?? []
            );
            $projectMembers = array_diff($projectMembers, [$user->id]);

            $startDay = $this->start_day;
            $endDay = $this->end_day;
            $leaves = Leave::whereIn('user_id', $projectMembers)
                ->where('status_of_leave', '!=', 'rejected')
                ->where(function ($query) use ($startDay, $endDay) {
                    $query->whereBetween('start_day', [$startDay, $endDay])
                        ->orWhereBetween('end_day', [$startDay, $endDay])
                        ->orWhere(function ($q) use ($startDay, $endDay) {
                            $q->where('start_day', '<=', $startDay)
                              ->where('end_day', '>=', $endDay);
                        });
                })
                ->with(['user.profile'])
                ->get();

            foreach ($leaves as $leave) {
                $projectOverlaps[] = [
                    'name' => $leave->user->profile->first_name . ' ' . $leave->user->profile->last_name,
                    'project' => $project->name,
                    'start_day' => $leave->start_day->format('d/m/Y'),
                    'end_day' => $leave->end_day->format('d/m/Y')
                ];
            }
        }

        if (!empty($projectOverlaps)) {
            $messages = [];
            foreach ($projectOverlaps as $overlap) {
                $messages[] = "{$overlap['name']} du projet {$overlap['project']} est en congé du {$overlap['start_day']} au {$overlap['end_day']}";
            }
            return implode("\n", $messages);
        }

        // Check team overlaps if no project overlaps
        $startDay = $this->start_day;
        $endDay = $this->end_day;
        $teamOverlaps = Leave::whereIn('user_id', $user->team->employee_ids ?? [])
            ->where('user_id', '!=', $this->user_id)
            ->where('status_of_leave', '!=', 'rejected')
            ->where(function ($query) use ($startDay, $endDay) {
                $query->whereBetween('start_day', [$startDay, $endDay])
                    ->orWhereBetween('end_day', [$startDay, $endDay])
                    ->orWhere(function ($q) use ($startDay, $endDay) {
                        $q->where('start_day', '<=', $startDay)
                          ->where('end_day', '>=', $endDay);
                    });
            })
            ->with(['user.profile'])
            ->get();

        if ($teamOverlaps->isNotEmpty()) {
            $messages = [];
            foreach ($teamOverlaps as $leave) {
                $messages[] = "{$leave->user->profile->first_name} {$leave->user->profile->last_name} est en congé du {$leave->start_day->format('d/m/Y')} au {$leave->end_day->format('d/m/Y')}";
            }
            return implode("\n", $messages);
        }

        $projectOverlaps = Leave::whereIn('user_id', $projectMembers)
            ->where('user_id', '!=', $this->user_id)
            ->where('status_of_leave', '!=', 'rejected')
            ->where(function ($query) {
                $query->whereBetween('start_day', [$this->start_day, $this->end_day])
                    ->orWhereBetween('end_day', [$this->start_day, $this->end_day])
                    ->orWhere(function ($q) {
                        $q->where('start_day', '<=', $this->start_day)
                          ->where('end_day', '>=', $this->end_day);
                    });
            })
            ->with('user.profile')
            ->get();

        if ($projectOverlaps->isNotEmpty()) {
            $names = $projectOverlaps->map(function($leave) {
                return $leave->user->profile->first_name . ' ' . $leave->user->profile->last_name;
            })->implode(', ');
            return "Cette période chevauche les congés de votre collègue de projet : $names";
        }

        return "Chevauchement de congés détecté";
    }

    public function getIsOverlappingAttribute(): bool
    {
        if (!$this->start_day || !$this->end_day) {
            return false;
        }

        return self::hasTeamOverlap($this->start_day, $this->end_day, $this->user_id);
    }

    public static function hasTeamOverlap($startDay, $endDay, $userId): bool
    {
        $user = User::find($userId);

        if (!$user || !$user->team) {
            return false;
        }

        $teamMembers = $user->team->employee_ids ?? [];

        if (empty($teamMembers)) {
            return false;
        }

        // Check team members leaves
        $hasTeamOverlap = self::whereIn('user_id', $teamMembers)
            ->where('status_of_leave', '!=', 'rejected')
            ->where(function ($query) use ($startDay, $endDay) {
                $query->whereBetween('start_day', [$startDay, $endDay])
                    ->orWhereBetween('end_day', [$startDay, $endDay])
                    ->orWhere(function ($q) use ($startDay, $endDay) {
                        $q->where('start_day', '<=', $startDay)
                          ->where('end_day', '>=', $endDay);
                    });
            })
            ->exists();

        if ($hasTeamOverlap) {
            return true;
        }

        // Check project members leaves
        $projectMembers = Project::where('status', 'active')
            ->where(function($query) use ($userId) {
                $query->where('manager_id', $userId)
                    ->orWhereJsonContains('member_ids', $userId);
            })
            ->get()
            ->flatMap(function($project) {
                return array_merge(
                    [$project->manager_id], 
                    $project->member_ids ?? []
                );
            })
            ->unique()
            ->values()
            ->toArray();

        if (empty($projectMembers)) {
            return false;
        }

        return self::whereIn('user_id', $projectMembers)
            ->where('user_id', '!=', $userId)
            ->where('status_of_leave', '!=', 'rejected')
            ->where(function ($query) use ($startDay, $endDay) {
                $query->whereBetween('start_day', [$startDay, $endDay])
                    ->orWhereBetween('end_day', [$startDay, $endDay])
                    ->orWhere(function ($q) use ($startDay, $endDay) {
                        $q->where('start_day', '<=', $startDay)
                          ->where('end_day', '>=', $endDay);
                    });
            })
            ->exists();
    }

    public function countWorkingDays(): int
    {
        if (!$this->start_day || !$this->end_day) {
            return 0;
        }

        $startDate = Carbon::parse($this->start_day);
        $endDate   = Carbon::parse($this->end_day);
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
