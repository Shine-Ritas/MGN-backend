<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\UserLoginRequest;
use App\Http\Requests\UserRegistrationRequest;
use App\Services\Auth\Authentication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(protected Authentication $authService) {}

    public function login(UserLoginRequest $request): JsonResponse
    {
        $this->authService->setRequest($request);

        return $this->authService->returnResponse('api')->signIn('web', '');
    }

    public function register(UserRegistrationRequest $request): JsonResponse
    {
        $request->validate([
            'user_code' => 'unique:users,user_code',
            'password' => 'required|string|min:8',
        ]);

        $this->authService->setRequest($request);

        return $this->authService->returnResponse('api')->signUp('web', '');
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->setRequest($request);

        return $this->authService->returnResponse('api')->signOut();
    }
}
