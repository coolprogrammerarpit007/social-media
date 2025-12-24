<?php

namespace App\Models;

use App\Models\Comment;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = [
        'user_id',
        'content',
        'media_path',
        'visibility',
    ];

    protected $appends = ['media_url'];

    protected function getMediaUrlAttribute()
    {
        return $this->media_path ? env('APP_URL') . '/' . $this->media_path : null;
    }

    protected function casts(): array
    {
        return [
            'deleted_at' => 'datetime',
        ];
    }


    public static function getPostDetails($id)
    {
        $data = static::with('user:id,email')->where('id',$id)->first(['id','content','media_path','visibility','user_id']);
        return $data;
    }


    /***********************
     *
     *
     *
     *
            Relationships
     */



    public function user()
    {
        return $this->belongsTo(User::class,'user_id','id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class,'post_id','id');
    }

    public function likes()
    {
        return $this->morphMany(Like::class,'likeable');
    }



}
