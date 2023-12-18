<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register the APIUserInterface with APIUserRepository
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            'App\Interfaces\APIUserInterface',
            'App\Repositories\APIUserRepository'
        );

        $this->app->bind(
            'App\Interfaces\ProductInterface',
            'App\Repositories\ProductRepository'
        );
    }
}
