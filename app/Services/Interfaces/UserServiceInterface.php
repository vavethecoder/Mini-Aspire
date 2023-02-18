<?php

namespace App\Services\Interfaces;

use App\Models\User;
use Illuminate\Http\Request;

interface UserServiceInterface
{
    public function createUser(Request $request): User;

    public function getFreshToken(Request $request): string;

    public function getRefreshToken(Request $request): string;

    public function userLogout(Request $request): void;
}
