<?php

namespace Leeduc\JsonApiBuilder;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\App;

class JsonApiBuilderServiceProvider extends ServiceProvider
{
    protected $events;
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/resources/views', 'jsonapi');
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('leeduc.jsonapibuilder', function ($app) {
            return new \Leeduc\JsonApiBuilder\JsonApiBuilder\Generate($app->request, $app->view);
        });
    }
}
