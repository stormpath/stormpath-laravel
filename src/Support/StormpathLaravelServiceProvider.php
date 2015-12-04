<?php

namespace Stormpath\Laravel\Support;

use Illuminate\Support\ServiceProvider;
use Stormpath\Client;
use Stormpath\Stormpath;

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
        $this->app['router']->middleware('stormpath.auth', \Stormpath\Laravel\Http\Middleware\Authenticate::class);
        $this->app['router']->middleware('stormpath.guest', \Stormpath\Laravel\Http\Middleware\RedirectIfAuthenticated::class);
        $this->registerConfig();
        $this->registerClient();

        $this->registerApplication();
        $this->enhanceConfig();



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
        $this->app->bind('stormpath.application', function() {
            if(config('stormpath.application')) {
                return \Stormpath\Resource\Application::get(config( 'stormpath.application' ));
            }
            return null;
        });
    }

    private function enhanceConfig()
    {
        if(!config('stormpath.application')) {
            return null;
        }

        $application = app('stormpath.application');
        $value = false;

        $accountStoreMappings = $application->accountStoreMappings;

        if ($accountStoreMappings) {
            foreach ($accountStoreMappings as $asm) {
                $directory = $asm->accountStore;
                $acp = $directory->accountCreationPolicy;
                $value = $acp->verificationEmailStatus == Stormpath::ENABLED ? true : $value;
            }
        }


        config(['stormpath.web.verifyEmail.enabled'=>$value]);
    }


}