<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    use ApiResponse;


    public function show(string $username)
    {
        try
        {

        }

        catch(\Exception $e)
        {
            return $this->error('some error occur,try again later',$e->getMessage(),500);
        }
    }
}
