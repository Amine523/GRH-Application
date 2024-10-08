<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Leave extends Model
{
    use SoftDeletes;

    // The attributes that are mass assignable.
    protected $fillable = [
        'user_id',
        'start_day',
        'end_day',
        'type_of_leave',
        'status_of_leave'
    ];

    // Relation to the User model (one leave belongs to one user)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
