<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticated;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticated
{
    use HasFactory, Notifiable, SoftDeletes, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'password',
        'valid_balance',
        'team_id',
        'authorization_hours',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    /**
     * Get the primary team this user belongs to based on team_id
     */
    public function team()
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    /**
     * Get all teams where the user is a project manager
     */
    public function managedTeams()
    {
        if (!$this->isProjectManager()) {
            return collect();
        }
        
        return Team::where('project_manager_id', $this->id)->get();
    }

    /**
     * Get all teams this user belongs to (including managed teams and teams they're a member of)
     */
    public function teams()
    {
        return Team::where('project_manager_id', $this->id)
            ->orWhereJsonContains('employee_ids', (string)$this->id)
            ->orWhereJsonContains('employee_ids', $this->id)
            ->get();
    }

    /**
     * Check if the user is a project manager of any team
     */
    public function isProjectManager(): bool
    {
        return $this->hasRole('project_manager');
    }

    public function leaves()
    {
        return $this->hasMany(Leave::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }


    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->profile ? "{$this->profile->first_name} {$this->profile->last_name}" : null,
        );
    }
 
}

//     public function teams(){
//         return $this->belongsToMany(Team::class);
//     }
// public function managedTeams() {
//     return $this->hasMany(Team::class,'project_manager_id');
// }
// public function isProjectManager():bool{
//     return $this->hasRole('project manager');
// }
// public function leaveRequests(){
//     return $this->hasMany(leaveRequests::class);
// }

// protected function fullName(): Attribute
// {
//     return Attribute::make(
//         get: fn() => $this->profile ? "{$this->profile->first_name} {$this->profile->last_name}" : null,
//     );
// }