<?php

namespace Addworking\LaravelClassFinder;

use Addworking\LaravelClassFinder\ClassFinder;
use Illuminate\Support\ServiceProvider;

class ClassFinderServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('class-finder', function ($app) {
            return ClassFinder::usingAutoload(base_path());
        });
    }

    public function provides()
    {
        return ['class-finder'];
    }
}
