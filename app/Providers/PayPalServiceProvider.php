<?php namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class PayPalServiceProvider extends ServiceProvider {

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('\App\Repositories\PayPal\Contracts\PayPalInterface', function()
        {
            return new \App\Repositories\PayPal\PayPalRepository;
        });
    }

}
