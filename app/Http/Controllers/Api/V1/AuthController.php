<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;

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
}
