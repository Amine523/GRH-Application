<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Models\Role;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'role_id',
        'first_name',
        'last_name',
        'phone_number',
        'profile_picture',
        'address',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}
