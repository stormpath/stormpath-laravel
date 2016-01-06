<?php
/*
 * Copyright 2015 Stormpath, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Stormpath\Laravel\Support;

use Illuminate\Support\ServiceProvider;
use Stormpath\Client;
use Stormpath\Stormpath;

class StormpathLaravelServiceProvider extends ServiceProvider
{
    const INTEGRATION_NAME = 'stormpath-laravel';
    const INTEGRATION_VERSION = '0.1.0';


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
        $this->app['router']->middleware('stormpath.produces', \Stormpath\Laravel\Http\Middleware\Produces::class);
        $this->registerConfig();
        $this->registerClient();
        $this->registerApplication();
        $this->registerUser();
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
        $id = config( 'stormpath.client.apiKey.id' );
        $secret = config( 'stormpath.client.apiKey.secret' );

        Client::$apiKeyProperties = "apiKey.id={$id}\napiKey.secret={$secret}";
        Client::$integration = self::INTEGRATION_NAME."/".self::INTEGRATION_VERSION;

        $this->app->singleton('stormpath.client', function() {
            return Client::getInstance();
        });
    }

    private function registerConfig()
    {
        $this->publishes([
            __DIR__.'/../config/stormpath.php' => config_path('stormpath.php'),
        ]);

        $this->mergeConfigFrom(
            __DIR__.'/../config/stormpath.php',
            'stormpath'
        );
    }

    private function registerApplication()
    {
        $this->app->bind('stormpath.application', function() {
            if(config('stormpath.application.href') == null) {
                throw new \InvalidArgumentException('Application href MUST be set.');
            }

            if(!$this->isValidApplicationHref()) {
                throw new \InvalidArgumentException(config('stormpath.application.href') . ' is not a valid Stormpath Application HREF.');
            }

            $application = \Stormpath\Resource\Application::get(config( 'stormpath.application.href' ));
            $this->enhanceConfig($application);
            return $application;

        });
    }

    private function registerUser()
    {
        $this->app->bind('stormpath.user', function($app) {
            $cookie = $app->request->cookie(config('stormpath.web.accessTokenCookie.name'));

            if(null === $cookie) {
                return null;
            }

            try {
                $result = (new \Stormpath\Oauth\VerifyAccessToken(app('stormpath.application')))->verify($cookie->getValue());
                return $result->getAccount();
            } catch (\Exception $e) {}

            return null;

        });
    }

    private function enhanceConfig($application)
    {
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

    private function isValidApplicationHref()
    {
        return !! strpos(config( 'stormpath.application.href' ), '/applications/');
    }


}
