<?php

namespace PhpSoft\JsonApiBuilder;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\App;

class JsonApiBuilderServiceProvider extends ServiceProvider
{
    protected $namespace = 'PhpSoft\JsonApiBuilder\Http\Controllers';

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
        $this->app->singleton('phpsoft.jsonapibuilder', function ($app) {
            return new \PhpSoft\JsonApiBuilder\JsonApiBuilder\JsonApiBuilder;
        });
    }
}
