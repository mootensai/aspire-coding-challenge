<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class LogoutController extends Controller
{
    /**
     * Handle the incoming logout request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        try {
            $removeToken = JWTAuth::invalidate(JWTAuth::getToken());
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage().'.'
            ]);
        }
        
        if ($removeToken) {
            return response()->json([
                'success' => true,
                'message' => 'Logout success.'
            ]);
        }
    }
}
