<?php

namespace App\Traits;

trait ApiResponse
{
    protected function success($message="success",$data=[],$status=200)
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data
        ],$status);
    }


    protected function error($message="Some error occurs!",$errors =[],$status=400)
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'errors' => $errors
        ],$status);
    }
}
