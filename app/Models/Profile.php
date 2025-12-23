<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

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

    protected function casts(): array
    {
        return [
            'dob' => 'date',
        ];
    }

    protected $appends = ['avatar_url','user_dob'];

    protected function getAvatarUrlAttribute()
    {
        return $this->avtar ? env('APP_URL').'/'.$this->avtar : null;
    }

    protected function getUserDobAttribute()
    {
        $formatted = Carbon::parse($this->dob)->format('M d, Y');
        return $this->dob ? $formatted : null;
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }



    // Model Methods




    public static function getPublicProfile($username)
    {

        $data =  static::where('user_name',$username)->where('is_public',1)->first(['id','full_name','dob','bio','avtar']);

        return $data;
    }
}
