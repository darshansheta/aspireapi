<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Exceptions\AspireException;
use Illuminate\Validation\ValidationException;
use Auth;

class AuthService
{
    /**
     * Register customer
     *
     * @return User
     */
    public function register(array $data): User
    {
        try {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);
        } catch (Exception $e) {
            throw new AspireException($e);
        }

        return $user;
    }

    public function login(array $data): array
    {
        if (!Auth::attempt($data)) {
            throw ValidationException::withMessages([
                'credentials are incorrect.',
            ]);
        }

        $user = Auth::user();

        return [
            'user'  => $user,
            'token' => $user->createToken(config('aspire.token_name'))->plainTextToken
        ];
    }

    public function logout(): void
    {
        Auth::user()->tokens()->delete();
    }
}
