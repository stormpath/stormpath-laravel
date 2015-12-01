<?php

namespace Stormpath\Laravel\Support;

use Illuminate\Support\ServiceProvider;
use Stormpath\Client;

class StormpathLaravelServiceProvider extends ServiceProvider
{
    const INTEGRATION_NAME = 'stormpath-laravel';
    const INTEGRATION_VERSION = '0.1.0-alpha1';


    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {

        $this->loadRoutes();

        $this->loadViewsFrom(__DIR__.'/../views', 'stormpath');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerConfig();
        $this->registerClient();
        $this->registerApplication();
    }

    public function provides()
    {
        return [
            'stormpath.client',
            'stormpath.application'
        ];
    }

    private function loadRoutes()
    {
        require __DIR__ . '/../Http/routes.php';
    }

    private function registerClient()
    {
        $id = config( 'stormpath.apiKey.id' );
        $secret = config( 'stormpath.apiKey.secret' );

        Client::$apiKeyProperties = "apiKey.id={$id}\napiKey.secret={$secret}";
        Client::$integration = self::INTEGRATION_NAME."/".self::INTEGRATION_VERSION;

        $this->app->singleton('stormpath.client', function() {
            return Client::getInstance();
        });
    }

    private function registerConfig()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/stormpath.php',
            'stormpath'
        );
    }

    private function registerApplication()
    {
        $this->app->singleton('stormpath.application', function() {
            return \Stormpath\Resource\Application::get(config( 'stormpath.application' ));
        });
    }


}