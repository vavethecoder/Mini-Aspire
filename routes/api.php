<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
    Route::post('register', 'register');
    Route::post('logout', 'logout');
    Route::post('refresh', 'refresh');
});

Route::controller(UserController::class)->group(function () {
    Route::get('user/loans', 'getAllLoans');
    Route::post('user/loan/apply', 'applyLoan');
    Route::post('user/loan/repayment', 'repaymentLoan');
});

Route::controller(AdminController::class)->group(function () {
    Route::get('admin/loans', 'getAllLoans');
    Route::patch('admin/loan/approve', 'approveLoan');
});
