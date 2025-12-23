<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
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

    public function me(Request $request)
    {
        try
        {
            $user = $request->user();
            $profile = $user->profile;

            return $this->success("User private data fetched successfully!",[
                'email' => $user->email,
                'username' => $profile->user_name ?? null,
                'fullname' => $profile->full_name ?? null,
                'dob' => $profile->dob ?? null,
                'bio' => $profile->bio ?? null,
                'avtar' => $profile->avatar_url ?? null,
                'is_public' => $profile->is_public ?? true
            ],200);

        }

        catch(\Exception $e)
        {
            return $this->error("some error occur",$e->getMessage(),500);
        }
    }


    public function update(Request $request)
    {

            $user = $request->user();
            $validated = Validator::make($request->all(),[
                'user_name' => 'sometimes|min:5|max:35|unique:profiles,user_name,' . optional($user->profile)->id,
                'full_name' => 'sometimes|min:5|max:35',
                'dob' => 'sometimes|string',
                'bio' => 'sometimes|min:10|max:100',
                'avtar_path' => 'sometimes|string',
                'is_public' => 'sometimes'
            ]);

            if($validated->fails())
            {
                return $this->error("Validation fails",$validated->errors(),422);
            }

            DB::beginTransaction();
            try{

                $profile = $user->profile ?? $user->profile()->create([
                    'is_public' => 1
                ]);

                $data = $validated->validate();

                if(isset($data['avtar_path']))
                {
                    $data['avtar'] = $this->createImageFromBase64($data['avtar_path'],'profile_avatars');
                    unset($data['avtar_path']);
                }

                $profile->update($data);
                DB::commit();


                return $this->success("Profile Updated successfully!",[
                    [
                        'username' => $profile->user_name,
                        'full_name' => $profile->full_name,
                        'dob' => $profile->dob,
                        'bio' => $profile->bio,
                        'avtar_path' => $profile->avtar ?? env('APP_URL') . '/' . $profile->avtar,
                        'avatar_url' => $profile->avatar_url
                    ],
                    200
                ]);

            }

        catch(\Exception $e)
        {
            DB::rollBack();
            return $this->error("failed to update!",$e->getMessage(),500);
        }
    }


    // Public profile

    public function profile(string $username)
    {
        try
        {
            $profile = Profile::getPublicProfile($username);
            return $this->success("public profile data fetched successfully!",$profile,200);
        }

        catch(\Exception $e)
        {
            return $this->error("error fetching user public profile data",$e->getMessage(),200);
        }
    }
}
