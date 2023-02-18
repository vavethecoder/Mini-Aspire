<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Services\Interfaces\UserServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserService implements UserServiceInterface
{
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function createUser(Request $request): User
    {
        $user = $this->userRepository->create($request->all());

        Log::channel('request')->info('Successfully user created for Correlation ID : ' . $request->header('X-Correlation-ID'));

        return $user;
    }

    public function getFreshToken(Request $request): string
    {
        $credentials = $request->only('email', 'password');
        $token = auth('api')->setTTL(120)->attempt($credentials);
        if (!$token) {
            Log::channel('request')->info('Auth token not generated for Correlation ID : ' . $request->header('X-Correlation-ID'));
        }

        Log::channel('request')->info('Successfully logged in for Correlation ID : ' . $request->header('X-Correlation-ID'));

        return $token;
    }

    public function getRefreshToken(Request $request): string
    {
        $token = auth('api')->refresh();
        if (!$token) {
            Log::channel('request')->info('Auth token not generated for Correlation ID : ' . $request->header('X-Correlation-ID'));
        }

        Log::channel('request')->info('Successfully auth token re-generated for Correlation ID : ' . $request->header('X-Correlation-ID'));

        return $token;
    }

    public function userLogout(Request $request): void
    {
        auth('api')->logout();

        Log::channel('request')->info('Successfully user logged out for Correlation ID : ' . $request->header('X-Correlation-ID'));
    }
}
