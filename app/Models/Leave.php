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
        'start_day' => 'date:Y-m-d',
        'end_day' => 'date:Y-m-d',
        'deleted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'authorization_hour' => 'float',
        'deduction_days' => 'float'
    ];

    protected $dateFormat = 'Y-m-d H:i:s';

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d');
    }

    public function setStartDayAttribute($value)
    {
        if ($value instanceof \DateTimeInterface) {
            $this->attributes['start_day'] = $value->format('Y-m-d');
        } elseif (is_string($value)) {
            // Try to parse the date, assuming it might be in d/m/Y format
            try {
                $date = Carbon::createFromFormat('d/m/Y', $value);
                $this->attributes['start_day'] = $date->format('Y-m-d');
            } catch (\Exception $e) {
                // If the d/m/Y format fails, try to parse it normally
                $this->attributes['start_day'] = Carbon::parse($value)->format('Y-m-d');
            }
        } else {
            $this->attributes['start_day'] = null;
        }
    }

    public function setEndDayAttribute($value)
    {
        if ($value instanceof \DateTimeInterface) {
            $this->attributes['end_day'] = $value->format('Y-m-d');
        } elseif (is_string($value)) {
            // Try to parse the date, assuming it might be in d/m/Y format
            try {
                $date = Carbon::createFromFormat('d/m/Y', $value);
                $this->attributes['end_day'] = $date->format('Y-m-d');
            } catch (\Exception $e) {
                // If the d/m/Y format fails, try to parse it normally
                $this->attributes['end_day'] = Carbon::parse($value)->format('Y-m-d');
            }
        } else {
            $this->attributes['end_day'] = null;
        }
    }

    public function toArray()
    {
        $array = parent::toArray();
        
        // Format dates for frontend display
        if (!empty($array['start_day'])) {
            $array['start_day_formatted'] = Carbon::parse($array['start_day'])->format('d/m/Y');
        }
        if (!empty($array['end_day'])) {
            $array['end_day_formatted'] = Carbon::parse($array['end_day'])->format('d/m/Y');
        }
        
        return $array;
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

        // Check project member overlaps
        if (self::hasProjectMemberOverlap($this->start_day, $this->end_day, $this->user_id)) {
            // Get all projects where the user is a member or manager
            $projects = Project::where('status', 'active')
                ->where(function($query) use ($user) {
                    $query->where('manager_id', $user->id)
                        ->orWhereJsonContains('member_ids', (string)$user->id);
                })
                ->get();

            $projectMemberIds = [];
            foreach ($projects as $project) {
                $memberIds = $project->member_ids ?? [];
                if (!is_array($memberIds)) {
                    $memberIds = json_decode($memberIds, true) ?? [];
                }
                $projectMemberIds = array_merge($projectMemberIds, $memberIds, [$project->manager_id]);
            }
            $projectMemberIds = array_unique($projectMemberIds);
            $projectMemberIds = array_diff($projectMemberIds, [$user->id]);

            if (!empty($projectMemberIds)) {
                $overlappingLeaves = self::whereIn('user_id', $projectMemberIds)
                    ->where('status_of_leave', '!=', 'rejected')
                    ->where('user_id', '!=', $this->user_id)
                    ->where(function($query) {
                        $query->whereBetween('start_day', [$this->start_day, $this->end_day])
                            ->orWhereBetween('end_day', [$this->start_day, $this->end_day])
                            ->orWhere(function($q) {
                                $q->where('start_day', '<=', $this->start_day)
                                    ->where('end_day', '>=', $this->end_day);
                            });
                    })
                    ->with('user.profile')
                    ->get();

                if ($overlappingLeaves->isNotEmpty()) {
                    $names = $overlappingLeaves->map(function ($leave) {
                        return $leave->user->profile->first_name . ' ' . $leave->user->profile->last_name;
                    })->unique()->implode(', ');
                    
                    return "Un membre de votre projet a déjà un congé approuvé pendant cette période : $names";
                }
            }
        }

        // Check team overlaps
        if (self::hasTeamOverlap($this->start_day, $this->end_day, $this->user_id)) {
            $team = $user->team;
            if ($team) {
                $teamMemberIds = $team->employee_ids ?? [];
                if (is_array($teamMemberIds) && !empty($teamMemberIds)) {
                    $teamOverlaps = self::whereIn('user_id', $teamMemberIds)
                        ->where('status_of_leave', '!=', 'rejected')
                        ->where('user_id', '!=', $this->user_id)
                        ->where(function($query) {
                            $query->whereBetween('start_day', [$this->start_day, $this->end_day])
                                ->orWhereBetween('end_day', [$this->start_day, $this->end_day])
                                ->orWhere(function($q) {
                                    $q->where('start_day', '<=', $this->start_day)
                                        ->where('end_day', '>=', $this->end_day);
                                });
                        })
                        ->with('user.profile')
                        ->get();

                    if ($teamOverlaps->isNotEmpty()) {
                        $names = $teamOverlaps->map(function ($leave) {
                            return $leave->user->profile->first_name . ' ' . $leave->user->profile->last_name;
                        })->unique()->implode(', ');
                        
                        return "Un membre de votre équipe a déjà un congé approuvé pendant cette période : $names";
                    }
                }
            }
        }

        return "Chevauchement de congés détecté";
    }

    public function getIsOverlappingAttribute(): bool
    {
        if (!$this->start_day || !$this->end_day) {
            return false;
        }

        return self::hasTeamOverlap($this->start_day, $this->end_day, $this->user_id) || 
               self::hasProjectMemberOverlap($this->start_day, $this->end_day, $this->user_id);
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
            ->where(function ($query) use ($userId) {
                $query->where('manager_id', $userId)
                    ->orWhereJsonContains('member_ids', $userId);
            })
            ->get()
            ->flatMap(function ($project) {
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

    /**
     * Check if there are any overlapping leaves with other project members
     */
    public static function hasProjectMemberOverlap($startDay, $endDay, $userId): bool
    {
        $user = User::find($userId);
        if (!$user) {
            return false;
        }

        // Get all projects where the user is a member or manager
        $projects = Project::where('status', 'active')
            ->where(function($query) use ($user) {
                $query->where('manager_id', $user->id)
                    ->orWhereJsonContains('member_ids', (string)$user->id);
            })
            ->get();

        if ($projects->isEmpty()) {
            return false;
        }

        // Collect all project member IDs (including managers)
        $projectMemberIds = [];
        foreach ($projects as $project) {
            $memberIds = $project->member_ids ?? [];
            if (!is_array($memberIds)) {
                $memberIds = json_decode($memberIds, true) ?? [];
            }
            
            // Add project members and manager (if not already added)
            $projectMemberIds = array_merge(
                $projectMemberIds,
                $memberIds,
                [$project->manager_id]
            );
        }

        // Remove duplicates and the current user's ID
        $projectMemberIds = array_unique($projectMemberIds);
        $projectMemberIds = array_diff($projectMemberIds, [$userId]);

        if (empty($projectMemberIds)) {
            return false;
        }

        // Check for overlapping leaves with project members
        return self::whereIn('user_id', $projectMemberIds)
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
        $endDate = Carbon::parse($this->end_day);
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
