<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use ApiResponse;
    public function checkHealth()
    {
        try
        {
            return $this->success("Api is working successfully!");

        }

        catch(\Exception $e)
        {
            return $this->error("Error occurs, try again later!");
        }
    }


    public function register(Request $request)
    {
        try
        {

            $validator = Validator::make($request->all(),[
                'name' => 'required|string|min:5|max:25',
                'email'=>'required|email|unique:users,email',
                'password' => 'required|min:6|max:16'
            ]);

            if($validator->fails())
            {
                return $this->error("validation error",$validator->errors(),422);
            }

            $validated_data = $validator->validate();

            DB::beginTransaction();

            $user = User::create([
                'name' => $validated_data['name'],
                'email' => $validated_data['email'],
                'password' => $validated_data['password'],
            ]);

            $user_role = Role::where('name','user')->first();
            $user->roles()->attach($user_role->id);
            $token = $user->createToken('auth_token')->plainTextToken;

            DB::commit();

            return $this->success("User registered successfully!",[
                [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email
                ],
                ['token' => $token]
            ],201);
        }

        catch(\Exception $e)
        {
            DB::rollBack();
            return $this->error("some error occur! try again later",$e->getMessage(),500);
        }
    }


    public function login(Request $request)
    {
        try
        {
            $validator = Validator::make($request->all(),[
                'email' => 'required|email',
                'password' => 'required'
            ]);

            if($validator->fails())
            {
                return $this->error("Validation Error",$validator->errors(),422);
            }

            $validated_data = $validator->validate();

            // check if valid credentials

            $user = User::where('email',$validated_data['email'])->first();
            if(!$user)
            {
                return $this->error('Invalid Credentials');
            }

            if(!Hash::check($validated_data['password'],$user->password))
            {
                return $this->error('Invalid Credentials');
            }

            DB::transaction(function () use ($user) {
                $user->update([
                    'last_login_at' => Carbon::now()
                ]);
            });

            $token = $user->createToken('auth_token')->plainTextToken;

            return $this->success("User Login successfully!",[
                [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email
                ],
                ['token' => $token]
            ],200);


        }

        catch(\Exception $e)
        {
            return $this->error("something happen on login",$e->getMessage(),500);
        }
    }

    public function logout(Request $request)
    {
        try
        {

            $request->user()->currentAccessToken('auth_token')->delete();
            return $this->success('Logged Out Successfully');
        }

        catch(\Exception $e)
        {
            return $this->error('some error occurs',$e->getMessage(),500);
        }
    }
}
