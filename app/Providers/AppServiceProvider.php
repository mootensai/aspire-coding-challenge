<?php

namespace App\Providers;

use App\Models\WeeklyLoan;
use Illuminate\Support\ServiceProvider;
use Hashids\Hashids;
use Illuminate\Support\Facades\Route;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // register hashids as singleton
        $this->app->singleton('hashid', function () {
            return new Hashids(env('HASHIDS_SALT'), 6);
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Bind route to use hashids
        // Route::bind('wl_hashid', function($value){
        //     return WeeklyLoan::findOrFailByHashid($value);
        // });
    }
}
