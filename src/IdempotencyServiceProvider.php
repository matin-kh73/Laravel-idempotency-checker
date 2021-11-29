<?php

namespace Mtkh\IdempotencyHandler;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Mtkh\IdempotencyHandler\RedisUtils\ThrottleSimultaneousRequests;


class IdempotencyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config/idempotency.php', 'idempotency');
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function boot()
    {
        $this->publishes([__DIR__ .'/config/idempotency.php' => config_path('idempotency.php')], 'idempotency');
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('idempotency', ThrottleSimultaneousRequests::class);
    }
}
