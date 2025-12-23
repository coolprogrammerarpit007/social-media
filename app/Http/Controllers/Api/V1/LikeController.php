<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Traits\ApiResponse;
use App\Models\Comment;

class LikeController extends Controller
{
    use ApiResponse;
    public function toggle(Request $request)
    {
        $validated = Validator::make($request->all(),[
            'type' => 'required|in:post,comment',
            'id'   => 'required|integer',
        ]);

        if($validated->fails())
        {
            $this->error("Validation Fails!",$validated->errors(),422);
        }

        $user = $request->user();
        $validated = $validated->validate();

        try
        {
            $likable = match($validated['type'])
            {
                'post' => Post::find($validated['id']),
                'comment' => Comment::find($validated['id']),
            };

            // check if existing like then unlike it

            $existing_like = $likable->likes()->where('user_id',$user->id)->first();
            if($existing_like)
            {
                $existing_like->delete();
                return response()->json([
                    'status' => true,
                    'liked' => false,
                    'likes_count' => $likable->likes()->count()
                ]);
            }

            $likable->likes()->create([
                'user_id' => $user->id
            ]);

            return response()->json([
        'status' => true,
        'liked'  => true,
        'likes_count' => $likable->likes()->count(),
    ]);
        }

        catch(\Exception $e)
        {
            return $this->error("some error occur on liking",$e->getMessage(),500);
        }

    }
}
