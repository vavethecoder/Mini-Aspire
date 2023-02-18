<?php

namespace App\Http\Controllers;

use App\Services\Interfaces\UserServiceInterface;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    private $userService;

    public function __construct(UserServiceInterface $userService)
    {
        parent::__construct();
        $this->middleware('auth:api', ['except' => ['login', 'register']]);

        $this->userService = $userService;
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        $this->userService->createUser($request);

        return response()->json([
            'status' => config('enums.api_status')['SUCCESS'],
            'message' => config('messages.success')['REGISTERED'],
        ], Response::HTTP_CREATED);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $token = $this->userService->getFreshToken($request);
        if (!$token) {
            return response()->json([
                'status' => config('enums.api_status')['ERROR'],
                'message' => config('messages.error')['UNAUTHORIZED'],
            ], Response::HTTP_UNAUTHORIZED);
        }

        return response()->json([
            'status' => config('enums.api_status')['SUCCESS'],
            'authorisation' => [
                'token' => $token,
                'type' => config('enums.token_type')['BEARER'],
            ]
        ], Response::HTTP_OK);

    }

    public function refresh(Request $request)
    {
        $token = $this->userService->getRefreshToken($request);

        return response()->json([
            'status' => config('enums.api_status')['SUCCESS'],
            'authorisation' => [
                'token' => $token,
                'type' => config('enums.token_type')['BEARER'],
            ]
        ], Response::HTTP_OK);
    }

    public function logout(Request $request)
    {
        $this->userService->userLogout($request);

        return response()->json([
            'status' => config('enums.api_status')['SUCCESS'],
            'message' => config('messages.success')['LOG_OUT'],
        ], Response::HTTP_OK);
    }

}
