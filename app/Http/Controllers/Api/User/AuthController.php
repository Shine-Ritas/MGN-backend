<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\UserLoginRequest;
use App\Http\Requests\UserRegisterRequest;
use App\Http\Requests\UserRegistrationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct()
    {

    }

    public function login(UserLoginRequest $request): JsonResponse
    {
        $process =  new \App\Services\Auth\Authentication($request);

        return $process->returnResponse('api')->signIn('web', '');
    }

    public function register(UserRegistrationRequest $request): JsonResponse
    {
        $request->validate([
            'user_code' => "unique:users,user_code",
            'password' => 'required|string|min:8'
        ]);

        $process =  new \App\Services\Auth\Authentication($request);

        return $process->returnResponse('api')->signUp('web', '');
    }

    public function logout(Request $request): JsonResponse
    {
        $process =  new \App\Services\Auth\Authentication($request);

        return $process->returnResponse('api')->signOut();
    }
}
