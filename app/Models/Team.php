<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_name',
        'profile_picture'
    ];

    public function projectManager()
    {
        return $this->belongsTo(User::class, 'project_manager')->whereHas('roles', function($query) {
            $query->where('name', 'project_manager');
        });
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function canDeleteProjectManager()
    {
        return $this->users()->where('role', 'project_manager')->doesntExist();
    }
}
