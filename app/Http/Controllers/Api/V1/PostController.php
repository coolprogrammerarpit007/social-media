<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Post;

class PostController extends Controller
{
    use ApiResponse;

    private function createImageFromBase64($base64, $folder)
    {
        $image_parts = explode(";base64,", $base64);
        $image_type_aux = explode("image/", $image_parts[0]);
        $image_type = $image_type_aux[1];
        $newCreatedImage = rand(10000, 99999) . '-' . time() . '-img.' . $image_type;
        $destinationPath = public_path('/uploads/' . $folder . '/') . $newCreatedImage;
        $data = base64_decode($image_parts[1]);
        // $data = $image_parts[1];
        file_put_contents($destinationPath, $data);
        $image_url = 'uploads/' . $folder . '/' . $newCreatedImage;
        return $image_url;
    }


    public function index()
    {
        try
        {
            $posts = Post::query()
                        ->select('id','user_id','content','media_path','visibility')
                        ->with('user.profile:id,user_name,full_name,user_id')
                        ->where('visibility','public')
                        ->orderBy('created_at','desc')
                        ->paginate(10);

            return $this->success("posts are fetched successfully!",$posts,200);
        }

        catch(\Exception $e)
        {
            return $this->error("something happen on the post fetching",$e->getMessage(),201);
        }


    }


    public function store(Request $request)
    {
        $user = $request->user();
        $validated = Validator::make($request->all(),[
            'content' => 'required|string|min:10|max:450',
            'media' => 'sometimes|string',
            'visibility' => 'sometimes|string'
        ]);

        if($validated->fails())
        {
            $this->error("Validation Fails!",$validated->errors(),422);
        }



        $data = $validated->validate();

        $data['user_id'] = $user->id;
        try
        {
            DB::beginTransaction();

            if(isset($data['media']))
            {
                $data['media_path'] = $this->createImageFromBase64($data['media'],'post_media');
                unset($data['media']);
            }
            $post = Post::create($data);
            DB::commit();
            return $this->success("post created successfully!",[
                'id' => $post->id,
                'media_url' => $post->media_url,
                'content' => $post->content,
                'visibility' => $post->visibility
            ],201);
        }

        catch(\Exception $e)
        {
            return $this->error("something happen on the post creation",$e->getMessage(),201);
        }
    }


    public function show(string $id)
    {
        try
        {
            $post = Post::getPostDetails($id);
            return $this->success("show single post successfully!",$post,200);
        }

        catch(\Exception $e)
        {
            return $this->error("something happen on showing post",$e->getMessage(),500);
        }
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $validator = Validator::make($request->all(),
        [
            'id' => 'required',
            'content' => 'sometimes|string|min:10|max:450',
            'media' => 'sometimes|string',
            'visibility' => 'sometimes|string'
        ]);

        if($validator->fails())
        {
            return $this->error("validation error",$validator->errors(),422);
        }
        try{
            $data = $validator->validate();
            $post = Post::where('id',$request->id)->where('user_id',$user->id)->first();
            DB::transaction(function () use ($data, $post) {
                $post->update($data);
            });
            return $this->success("post updated successfully",new PostResource($post),200);
        }

        catch(\Exception $e)
        {
            return $this->error("something occur on updating post",$e->getMessage(),500);
        }
    }

    public function destroy(Request $request)
    {
        try
        {
            $post = Post::find($request->id);
            $post->delete();
            return $this->success("post deleted successfully!",null,204);
        }

        catch(\Exception $e)
        {
            return $this->error("something happen on deleting post",$e->getMessage(),500);
        }
    }

}
