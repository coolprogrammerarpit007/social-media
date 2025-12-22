<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $fillable = [
        'user_id',
        'user_name',
        'full_name',
        'dob',
        'bio',
        'avtar',
        'is_public'
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
