<?php

namespace App\Providers;

use App\Services\Interfaces\LoanServiceInterface;
use App\Services\Interfaces\UserServiceInterface;
use App\Services\LoanService;
use App\Services\UserService;
use Illuminate\Support\ServiceProvider;

class BusinessServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(UserServiceInterface::class, UserService::class);
        $this->app->bind(LoanServiceInterface::class, LoanService::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
