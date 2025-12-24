<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Comment;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
class FeedController extends Controller
{
    use ApiResponse;

    public function feeds(Request $request)
    {
        try
        {
            $user = $request->user();

            // IDs of user I follow
            $followers_ids = $user->following()->pluck('following_id');

            $posts = Post::query()->with(['user:id,name','comments','likes'])->where(function($query) use ($user){
                $query->where('user_id',$user->id);
            })->orWhere(function ($query) use ($followers_ids) {
                $query->whereIn('user_id',$followers_ids)
                ->where('visibility','public');
            })->whereNull('deleted_at')
                ->orderBy('created_at','desc')
                ->get();

            return $this->success("posts fetched successfully!",$posts,200);
        }

        catch(\Exception $e)
        {
            return $this->error("something occur! while showing feeds",$e->getMessage(),500);
        }
    }
}
