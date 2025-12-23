<?php

namespace App\Models;

use Dom\Comment as DomComment;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $fillable = [
        'user_id',
        'post_id',
        'parent_id',
        'content'
    ];


    protected function casts(): array
    {
        return [
            'deleted_at' => 'datetime',
        ];
    }


    /*
        Relationships
    */

    public function post()
    {
        return $this->belongsTo(Post::class,'post_id','id');
    }

    public function user()
    {
        return $this->belongsTo(User::class,'user_id','id');
    }


    // Self relations

    public function parent()
    {
        return $this->belongsTo(Comment::class,'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(Comment::class,'parent_id')->whereNull('deleted_at');
    }

    public function likes()
    {
        return $this->morphMany(Like::class,'likeable');
    }

}
