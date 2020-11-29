<?php

namespace WhoisProxy;

use Illuminate\Support\ServiceProvider;

class WhoisProxyServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'btrsco');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'btrsco');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/whoisproxy.php', 'whoisproxy');

        // Register the service the package provides.
        $this->app->singleton('whoisproxy', function ($app) {
            return new WhoisProxy;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['whoisproxy'];
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole()
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__ . '/../config/whoisproxy.php' => config_path('whoisproxy.php'),
        ], 'whoisproxy.config');

        // Publishing the views.
        /*$this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/btrsco'),
        ], 'whoisproxy.views');*/

        // Publishing assets.
        /*$this->publishes([
            __DIR__.'/../resources/assets' => public_path('vendor/btrsco'),
        ], 'whoisproxy.views');*/

        // Publishing the translation files.
        /*$this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/btrsco'),
        ], 'whoisproxy.views');*/

        // Registering package commands.
        // $this->commands([]);
    }
}
