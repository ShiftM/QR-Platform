<?php namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RestServiceProvider extends ServiceProvider {

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
        $this->app->bind('\App\Repositories\Rest\Contracts\RestInterface', function()
        {
            return new \App\Repositories\Rest\RestRepository;
        });
    }

}
