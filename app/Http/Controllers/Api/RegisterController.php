<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    /**
     * Handle the incoming register new user (non-admin) request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        // set validation
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => [
                'required',
                Password::min(8)
                ->letters()
                ->mixedCase()
                ->numbers()
                ->symbols()
                ->uncompromised()
            ],
            'password_confirmation' => 'required|same:password'
        ]);

        // if validation fail return error, if success continue
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // create new user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);

        // if user creation fails return HTTP code 400
        if (!$user) {
            return response()->json([
                'success' => false,
            ], 400);
        }

        // user creation success return HTTP code 201
        return response()->json([
            'success' => true,
            'user' => $user,
        ], 201);
    }
}
