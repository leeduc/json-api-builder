<?php

namespace Leeduc\JsonApiBuilder;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\App;

class JsonApiBuilderServiceProvider extends ServiceProvider
{
    protected $namespace = 'Leeduc\JsonApiBuilder\Http\Controllers';

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('leeduc.jsonapibuilder', function ($app) {
            return new \Leeduc\JsonApiBuilder\JsonApiBuilder\JsonApiBuilder;
        });
    }
}
