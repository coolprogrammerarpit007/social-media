<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Validator;

class FollowController extends Controller
{
    use ApiResponse;

    public function toggle(Request $request)
    {
        try
        {
            $validator = Validator::make($request->all(),[
                'target_id' => 'required|integer|exists:users,id'
            ]);

            if($validator->fails())
            {
                return $this->error("Validation fails!",$validator->errors(),422);
            }


            $user = $request->user();
            $target_id = $request->target_id;

            $target_user = User::find($target_id);
            if(!$target_user)
            {
                return $this->error("Un-found User",[],404);
            }

            //  check if trying to follow yourself

            if($user->id == $target_user->id)
            {
                return $this->success("can-not follow yourself.",[],200);
            }

            //  check if already follow then unfollow otherwise follow
            $existing_follow = $user->following()->where('following_id',$target_id)->exists();
            if($existing_follow)
            {
                $user->following()->detach($target_id);
                return response()->json([
                    'status' => true,
                    'followed' => false,
                    'message' => 'unfollowed successfully!'
                ]);
            }

            $user->following()->attach($target_id);
            return response()->json([
                'status' => true,
                'message' => 'follow successfully!',
                'data' => $target_user->followers()->count(),
            ]);
        }

        catch(\Exception $e)
        {
            $this->error("something occur! try again later",$e->getMessage(),500);
        }
    }
}
