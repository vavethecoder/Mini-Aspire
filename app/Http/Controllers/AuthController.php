<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);
        $credentials = $request->only('email', 'password');

        $token = auth('api')
            ->setTTL(120)
            ->attempt($credentials);

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

    public function register(Request $request, UserService $userService)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        $userService->createUser($request);

        return response()->json([
            'status' => config('enums.api_status')['SUCCESS'],
            'message' => config('messages.success')['REGISTERED'],
        ], Response::HTTP_CREATED);
    }

    public function logout()
    {
        auth('api')->logout();
        return response()->json([
            'status' => config('enums.api_status')['SUCCESS'],
            'message' => config('messages.success')['LOG_OUT'],
        ], Response::HTTP_OK);
    }

    public function refresh()
    {
        return response()->json([
            'status' => config('enums.api_status')['SUCCESS'],
            'authorisation' => [
                'token' => auth('api')->refresh(),
                'type' => config('enums.token_type')['BEARER'],
            ]
        ], Response::HTTP_OK);
    }

}
