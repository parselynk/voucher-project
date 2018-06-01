<?php

namespace App\Providers;

use App\Offer;
use App\Voucher;
use App\User;



use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('App\Repositories\Contracts\OfferInterface', 'App\Repositories\OfferRepository');
        $this->app->when('App\Repositories\OfferRepository')->needs('$model')->give(function () {
            return new Offer;
        });

        $this->app->bind('App\Repositories\Contracts\VoucherInterface', 'App\Repositories\VoucherRepository');
        $this->app->when('App\Repositories\VoucherRepository')->needs('$model')->give(function () {
            return new Voucher;
        });

        $this->app->when('App\Repositories\VoucherRepository')->needs('$recipient')->give(function () {
            return new User;
        });
    }
}
