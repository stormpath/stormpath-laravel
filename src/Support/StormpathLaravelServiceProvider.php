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
use Stormpath\Laravel\Http\Helpers\IdSiteModel;
use Stormpath\Resource\AccountCreationPolicy;
use Stormpath\Stormpath;

class StormpathLaravelServiceProvider extends ServiceProvider
{
    const INTEGRATION_NAME = 'stormpath-laravel';
    const INTEGRATION_VERSION = '0.2.0';


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


        $this->checkForSocialProviders();

        $this->registerUser();
    }

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

            $application = \Stormpath\Resource\Application::get(config( 'stormpath.application.href'));

            $this->enhanceConfig($application);
            return $application;

        });
    }

    private function registerUser()
    {
        $this->app->bind('stormpath.user', function($app) {

            try {
                $spApplication = app('stormpath.application');
            } catch (\Exception $e) {
                return null;
            }

            $cookie = $app->request->cookie(config('stormpath.web.accessTokenCookie.name'));

            if(null === $cookie) {
                $cookie = $this->refreshCookie($app->request);
            }

            try {
                if($cookie instanceof \Symfony\Component\HttpFoundation\Cookie) {
                    $cookie = $cookie->getValue();
                }
                $result = (new \Stormpath\Oauth\VerifyAccessToken($spApplication))->verify($cookie);
                return $result->getAccount();
            } catch (\Exception $e) {}

            return null;

        });
    }

    private function refreshCookie($request)
    {
        $cookie = $request->cookie(config('stormpath.web.refreshTokenCookie.name'));
        if($cookie instanceof \Symfony\Component\HttpFoundation\Cookie)
            $cookie = $cookie->getValue();

        try {
            $refreshGrant = new \Stormpath\Oauth\RefreshGrantRequest($cookie);
            $auth = new \Stormpath\Oauth\RefreshGrantAuthenticator(app('stormpath.application'));
            $result = $auth->authenticate($refreshGrant);

            $this->setNewAccessToken($request, $result);

            return $result->getAccessTokenString();

        } catch(\Stormpath\Resource\ResourceError $re) {
            return null;
        }
    }

    private function setNewAccessToken($request, $cookies)
    {
        $cookieJar = app('cookie');
        $cookieJar->queue(
            cookie(
                config('stormpath.web.accessTokenCookie.name'),
                $cookies->getAccessTokenString(),
                $cookies->getExpiresIn(),
                config('stormpath.web.accessTokenCookie.path'),
                config('stormpath.web.accessTokenCookie.domain'),
                config('stormpath.web.accessTokenCookie.secure'),
                config('stormpath.web.accessTokenCookie.httpOnly')
            )

        );


        $request->cookies->add([config('stormpath.web.accessTokenCookie.name') => $cookies->getAccessTokenString() ]);

    }

    private function enhanceConfig($application)
    {
        $asm = $application->getDefaultAccountStoreMapping();

        if(null === $asm && config('stormpath.web.register.enabled')) {
            throw new \InvalidArgumentException('No default account store is mapped to the specified application. A default account store is required for registration.');
        }

        if(!config('stormpath.web.register.enabled')) {
            return false;
        }

        $directory = \Stormpath\Resource\Directory::get($asm->accountStore->href, ['expand'=>'accountCreationPolicy']);

        $value = $directory->verificationEmailStatus == Stormpath::ENABLED ?: false;

        config(['stormpath.web.verifyEmail.enabled'=>$value]);
    }

    private function isValidApplicationHref()
    {
        return !! strpos(config( 'stormpath.application.href' ), '/applications/');
    }

    private function checkForSocialProviders()
    {
        $model = IdSiteModel::get(app('stormpath.application')->getProperty('idSiteModel')->href);
        $providers = $model->getProperty('providers');


        foreach($providers as $provider) {
            config(['stormpath.web.socialProviders.enabled' => true]);
            require __DIR__ . '/../Http/socialRoutes.php';

            switch ($provider->providerId) {
                case 'facebook' :
                    $this->setupFacebookProvider($provider);
                    break;
                case 'google' :
                    $this->setupGoogleProvider($provider);
                    break;
            }
        }


    }

    private function setupFacebookProvider($provider)
    {
        config(['stormpath.web.socialProviders.facebook.enabled' => true]);
        config(['stormpath.web.socialProviders.facebook.name' => 'Facebook']);
        config(['stormpath.web.socialProviders.facebook.clientId' => $provider->clientId]);
    }

    private function setupGoogleProvider($provider)
    {
        config(['stormpath.web.socialProviders.google.enabled' => true]);
        config(['stormpath.web.socialProviders.google.name' => 'Google']);
        config(['stormpath.web.socialProviders.google.clientId' => $provider->clientId]);
        config(['stormpath.web.socialProviders.google.callbackUri' => $provider->redirectUri]);
    }


}
