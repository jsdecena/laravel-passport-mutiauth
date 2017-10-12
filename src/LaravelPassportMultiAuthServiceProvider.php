<?php

namespace Jsdecena\LPM;

use Illuminate\Support\ServiceProvider;

class LaravelPassportMultiAuthServiceProvider extends ServiceProvider
{
    /**
     * Set up the publishing of configuration
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/database/migrations' => database_path('migrations')
        ], 'migrations');
    }

    /**
     *
     * @return void
     */
    public function register()
    {
        //
    }
}