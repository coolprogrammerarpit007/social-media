<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
class CommentController extends Controller
{
    use ApiResponse;


    public function store(Request $request)
    {

        $validator = Validator::make($request->all(),[
            'content' => 'required|string|min:2|max:100',
            'post_id' => 'required|integer|exists:posts,id',
            'parent_id' => 'sometimes|integer|exists:comments,id'
        ]);

        if($validator->fails())
        {
            return $this->error("Validation Error",$validator->errors(),422);
        }
        try
        {
            $user = $request->user();
            $data = $validator->validate();

            // if replying comment belongs to same post

            if(isset($data['parent_id']))
            {
                $parent = Comment::where('id',$data['parent_id'])->where('post_id',$data['post_id'])->first();

                if(!$parent)
                {
                    return $this->error("Invalid Parent Comment",null,422);
                }
            }


            $comment = Comment::create([
                'user_id' => $user->id,
                'post_id' => $data['post_id'],
                'parent_id' => $data['parent_id'] ?? null,
                'content' => $data['content']
            ]);


            return $this->success("comment created successfully!",[
                'id' => $comment->id,
                'content' => $comment->content
            ],201);
        }

        catch(\Exception $e)
        {
            return $this->error("error happen on comment creation",$e->getMessage(),500);
        }
    }


    public function update(Request $request,string $id)
    {
        try
        {
            $comment = Comment::find($id);
            DB::transaction(function() use ($comment,$request)
            {

                $comment->update($request->only('content'));
            });

            return $this->success("comment updated successfully!",[
                'id' => $comment->id,
                'content' => $comment->content
            ],200);
        }

        catch(\Exception $e)
        {
            return $this->error("error happen on comment updating",$e->getMessage(),500);
        }
    }


    public function index(string $post_id)
    {
        try
        {
            $comments = Comment::select('id','post_id','parent_id','content')->with([
                'user:id,name',
                'replies:id,post_id,parent_id,content'
            ])->where('post_id',$post_id)->whereNull('parent_id')->orderBy('created_at','desc')->paginate(10);
            return response()->json([
        'status' => true,
        'message' => 'Comments fetched successfully',
        'data' => $comments
    ]);
        }

        catch(\Exception $e)
        {
            return $this->error("error happen on comment fetching!",$e->getMessage(),500);
        }


    }
}
