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

use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Stormpath\Client;
use Stormpath\Laravel\Http\Helpers\IdSiteModel;
use Stormpath\Laravel\Http\Helpers\PasswordPolicies;
use Stormpath\Laravel\Http\Helpers\PasswordPolicy;
use Stormpath\Resource\AccountCreationPolicy;
use Stormpath\Resource\AccountStore;
use Stormpath\Resource\AccountStoreMapping;
use Stormpath\Stormpath;

class StormpathLaravelServiceProvider extends ServiceProvider
{
    const INTEGRATION_NAME = 'stormpath-laravel';
    const INTEGRATION_VERSION = '0.4.1';

    protected $defer = false;
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

        $this->app->resolving(EncryptCookies::class, function ($object) {
            $object->disableFor(config('stormpath.web.accessTokenCookie.name'));
            $object->disableFor(config('stormpath.web.refreshTokenCookie.name'));
        });

    }

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {

        $this->warmResources();

        $this->checkForSocialProviders();
        $this->setPasswordPolicies();
        $this->setAccountCreationPolicy();

        $this->loadViewsFrom(__DIR__.'/../views', 'stormpath');
        $this->loadRoutes();




    }

    public function provides()
    {
        return [
            'stormpath.client',
            'stormpath.application'
        ];
    }

    private function warmResources()
    {
        if(config('stormpath.application.href') == null)  return;
        $cache = $this->app['cache.store'];


        if($cache->has('stormpath.resourcesWarm') && $cache->get('stormpath.resourcesWarm') == true) return;

        app('stormpath.client');
        $application = app('stormpath.application');

        $dasm = AccountStoreMapping::get($application->defaultAccountStoreMapping->href);

        $mappings = $application->getAccountStoreMappings(['expand'=>'accountStore']);
        $accountStoreArray = [];

        foreach($mappings as $mapping) {
            $accountStoreArrayValues = [
                'href' => $mapping->accountStore->href,
                'name' => $mapping->accountStore->name
              ];

            if(isset($mapping->accountStore->provider)) {
                $accountStoreArrayValues['provider'] = [
                    'href' => $mapping->accountStore->provider->href,
                    'providerId' => $mapping->accountStore->provider->providerId
                  ];
            }

            $accountStoreArray[] = $accountStoreArrayValues;
        }


        $asm = AccountStoreMapping::get($application->accountStoreMappings->href,['expand'=>'accountStore']);

        $passwordPolicy = $dasm->getAccountStore()->getProperty('passwordPolicy');

        $accountCreationPolicy = $dasm->getAccountStore(['expand'=>'accountCreationPolicy'])->accountCreationPolicy;

        $passwordPolicies = PasswordPolicies::get($passwordPolicy->href);


        $cache->rememberForever('stormpath.defaultAccountStoreMapping', function() use ($dasm) {
            return $dasm;
        });

        $cache->rememberForever('stormpath.accountStoreMappings', function() use ($asm) {
            return $asm;
        });

        $cache->rememberForever('stormpath.accountStores', function() use ($accountStoreArray) {
            return $accountStoreArray;
        });

        $cache->rememberForever('stormpath.passwordPolicy', function() use ($passwordPolicy) {
            return $passwordPolicy;
        });

        $cache->rememberForever('stormpath.accountCreationPolicy', function() use ($accountCreationPolicy) {
            return $accountCreationPolicy;
        });

        $cache->rememberForever('stormpath.passwordPolicies', function() use ($passwordPolicies) {
            return $passwordPolicies;
        });

        $cache->rememberForever('stormpath.resourcesWarm', function() {
            return true;
        });
    }

    private function loadRoutes()
    {
        require __DIR__ . '/../Http/routes.php';

        if(config('stormpath.web.social.enabled')) {
            require __DIR__ . '/../Http/socialRoutes.php';
        }
    }

    private function registerClient()
    {
        $id = config( 'stormpath.client.apiKey.id' );
        $secret = config( 'stormpath.client.apiKey.secret' );

        Client::$apiKeyProperties = "apiKey.id={$id}\napiKey.secret={$secret}";
        Client::$integration = $this->buildAgent();


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
        $this->app->singleton('stormpath.application', function() {
            $this->guardAgainstInvalidApplicationHref();
//            return $this->app['cache.store']->rememberForever('stormpath.application', function() {
                $application = \Stormpath\Resource\Application::get(config('stormpath.application.href'));
                return $application;
//            });
        });

    }

    private function guardAgainstInvalidApplicationhref()
    {
        if (config('stormpath.application.href') == null) {
            throw new \InvalidArgumentException('Application href MUST be set.');
        }

        if (!$this->isValidApplicationHref()) {
            throw new \InvalidArgumentException(config('stormpath.application.href') . ' is not a valid Stormpath Application HREF.');
        }
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

    private function isValidApplicationHref()
    {
        return !! strpos(config( 'stormpath.application.href' ), '/applications/');
    }

    private function setPasswordPolicies()
    {

        if(config('stormpath.web.forgotPassword.enabled') == true) return;

        if(config('stormpath.web.changePassword.enabled') == true) return;

        if(config('stormpath.application.href') == null)  return;

        config(['stormpath.web.forgotPassword.enabled' => false]);
        config(['stormpath.web.forgotPassword.enabled' => false]);

        $cache = $this->app['cache.store'];

        $passwordPolicies = $cache->get('stormpath.passwordPolicies');

        if($passwordPolicies->getProperty('resetEmailStatus') == Stormpath::ENABLED) {
            config(['stormpath.web.forgotPassword.enabled' => true]);
            config(['stormpath.web.forgotPassword.enabled' => true]);
            return;
        }

    }

    private function setAccountCreationPolicy()
    {
        if(config('stormpath.web.verifyEmail.enabled') == true) return;

        $cache = $this->app['cache.store'];

        if(!$cache->has('stormpath.accountCreationPolicy')) {
            $this->warmResources();
        }

        config(['stormpath.web.verifyEmail.enabled' => false]);

        $accountCreationPolicy = $cache->get('stormpath.accountCreationPolicy');

        if($accountCreationPolicy == null) {
            return;
        }


        if($accountCreationPolicy->verificationEmailStatus == Stormpath::ENABLED) {
            config(['stormpath.web.verifyEmail.enabled' => true]);
            return;
        }

    }


    private function checkForSocialProviders()
    {
        if(config('stormpath.application.href') == null)  return;

        $model = app('cache.store')->rememberForever('stormpath.idsitemodel', function() {
            $idSiteModel = $this->getIdSiteModel();
            return IdSiteModel::get($idSiteModel->href);
        });

        $providers = $model->getProperty('providers');


        foreach($providers as $provider) {
            config(['stormpath.web.social.enabled' => true]);

            switch ($provider->providerId) {
                case 'facebook' :
                    $this->setupFacebookProvider($provider);
                    break;
                case 'google' :
                    $this->setupGoogleProvider($provider);
                    break;
                case 'github' :
                    Log::info('Github is not yet supported inside of the Laravel SDK');
//                    $this->setupGithubProvider($provider);
                    break;
                case 'linkedin' :
                    $this->setupLinkedinProvider($provider);
                    break;
            }
        }


    }

    private function getIdSiteModel()
    {
        $model = app('stormpath.application')->getProperty('idSiteModel');

        if($model == null) {
            throw new \InvalidArgumentException('ID Site could not initialize, please visit ID Site from the Stormpath Dashboard and then clear your cache');
        }

        return $model;

    }

    private function setupFacebookProvider($provider)
    {
        config(['stormpath.web.social.facebook.enabled' => true]);
        config(['stormpath.web.social.facebook.name' => 'Facebook']);
        config(['stormpath.web.social.facebook.clientId' => $provider->clientId]);
    }

    private function setupGoogleProvider($provider)
    {
        config(['stormpath.web.social.google.enabled' => true]);
        config(['stormpath.web.social.google.name' => 'Google']);
        config(['stormpath.web.social.google.clientId' => $provider->clientId]);
        config(['stormpath.web.social.google.callbackUri' => $provider->redirectUri]);
    }

//    private function setupGithubProvider($provider)
//    {
//        config(['stormpath.web.social.github.enabled' => true]);
//        config(['stormpath.web.social.github.name' => 'Github']);
//        config(['stormpath.web.social.github.clientId' => $provider->clientId]);
//    }

    private function setupLinkedinProvider($provider)
    {
        config(['stormpath.web.social.linkedin.enabled' => true]);
        config(['stormpath.web.social.linkedin.name' => 'LinkedIn']);
        config(['stormpath.web.social.linkedin.clientId' => $provider->clientId]);

    }

    private function buildAgent()
    {
        $agent = [];

        if(request()->headers->has('X-STORMPATH-AGENT')) {
            $agent[] = request()->header('X-STORMPATH-AGENT');
        }

        $laravel = app();
        $version = $laravel::VERSION;

        $agent[] = self::INTEGRATION_NAME . '/' . self::INTEGRATION_VERSION;
        $agent[] = 'laravel/' . $version;

        return implode(' ', $agent);
    }


}
