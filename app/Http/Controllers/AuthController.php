<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;

class AuthController extends Controller
{
    public function __construct(protected AuthService $authService) {}

    public function register(RegisterRequest $request): UserResource
    {
        $user = $this->authService->register($request->all());

        return (new UserResource($user))->additional([
            'message' => 'Registered successfully!'
        ]);
    }

    public function login(LoginRequest $request): Response
    {
        $responseData = $this->authService->login($request->all());

        return response($responseData);
    }

    public function logout(): Response
    {
        $this->authService->logout();

        return [
            'message' => 'Token Revoked'
        ];
    }
}
