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
    
    protected $casts = [
        'employee_ids' => 'array',
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

    public function projectManager()
    {
        return $this->belongsTo(User::class, 'project_manager_id');
    }

    /**
     * The users that belong to the team.
     */
    public function users()
    {
        $userIds = is_array($this->employee_ids) ? $this->employee_ids : [];
        return User::whereIn('id', $userIds);
    }
    
    /**
     * Alias for backward compatibility
     */
    public function members()
    {
        return $this->users();
    }
    
    /**
     * Alias pour la rétrocompatibilité
     */
    public function employees()
    {
        return $this->members();
    }

    /**
     * Récupère les membres avec leurs profils
     */
    public function membersWithProfiles()
    {
        return $this->members()->load('profile');
    }
    
    /**
     * Vérifie si un utilisateur est membre de l'équipe
     */
    public function hasMember(User $user): bool
    {
        return in_array($user->id, $this->employee_ids ?? []);
    }
}
