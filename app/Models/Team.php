<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_name',
        'project_manager_id',
        'employee_ids'
    ];
    
    // Map 'name' to 'team_name' for backward compatibility
    public function getNameAttribute()
    {
        return $this->team_name;
    }
    
    public function setNameAttribute($value)
    {
        $this->attributes['team_name'] = $value;
    }

    protected $casts = [
        'employee_ids' => 'array',
    ];


    public function projectManager()
    {
        return $this->belongsTo(User::class, 'project_manager_id');
    }

    public function employees()
    {
        return $this->belongsToMany(User::class, 'team_user', 'team_id', 'user_id')
            ->withTimestamps()
            ->withPivot('is_project_manager')
            ->using(TeamUser::class);
    }
    public function users()
    {
        return $this->hasMany(User::class);
    }


    // Get employees with their profiles
    public function employeesWithProfiles()
    {
        return $this->employees()->with('profile');
    }
}
