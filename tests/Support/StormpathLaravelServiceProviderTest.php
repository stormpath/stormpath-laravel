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

namespace Stormpath\Laravel\Tests\Support;

use Illuminate\Config\Repository;
use Illuminate\Foundation\Application;
use Stormpath\Laravel\Support\StormpathLaravelServiceProvider;
use Stormpath\Laravel\Tests\TestCase;
use Stormpath\Stormpath;

class StormpathLaravelServiceProviderTest extends TestCase
{
    /** @test */
    public function it_tells_us_what_it_provides()
    {
        $provider = $this->setupServiceProvider($this->app);
        $provides = $provider->provides();

        $this->assertContains('stormpath.client', $provides);
        $this->assertContains('stormpath.application', $provides);
    }

    /** @test */
    public function it_provides_an_instance_of_client()
    {
        $client = app('stormpath.client');

        $this->assertInstanceOf('\Stormpath\Client', $client);

    }

    /** @test */
    public function it_provides_an_instance_of_application()
    {
        $this->setupStormpathApplication();
        $application = app('stormpath.application');

        $this->assertInstanceOf('\Stormpath\Resource\Application', $application);
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function it_throws_exception_from_stormpath_application_if_application_is_not_set()
    {
        app('stormpath.application');

        $this->assertArrayNotHasKey('enabled', config('stormpath.web.verifyEmail'));
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function it_throws_exception_if_stormpath_applicaiton_is_not_full_url()
    {
        config(['stormpath.application.href'=>'123456789']);
        app('stormpath.application');
    }

    /** @test */
    public function it_returns_null_when_getting_user_without_an_application_set()
    {
        $user = app('stormpath.user');
        $this->assertNull($user);
    }

    /** @test */
    public function it_sets_verify_email_config_to_false_by_default()
    {
        $this->setupStormpathApplication();
        app('stormpath.application');
        $this->assertArrayHasKey('enabled', config('stormpath.web.verifyEmail'));
        $this->assertFalse(config('stormpath.web.verifyEmail.enabled'));
    }

    /** @test */
    public function it_sets_verify_email_config_to_true_if_account_store_mapping_for_application_is_set_to_verify_email()
    {
        $this->setupStormpathApplication();
        $accountStoreMappings = $this->application->accountStoreMappings;

        try {
            if ($accountStoreMappings) {
                foreach ($accountStoreMappings as $asm) {
                    $directory = $asm->accountStore;
                    $acp = $directory->accountCreationPolicy;
                    $acp->verificationEmailStatus = Stormpath::ENABLED;
                    $acp->save();
                }
            }
        } catch (\Stormpath\Resource\ResourceError $re) {
            var_dump($re->getDeveloperMessage());
            var_dump($re->getMessage());
            var_dump($re->getStatus());
            throw $re;
        }

        $application = app('stormpath.application');

        $this->assertTrue(config('stormpath.web.verifyEmail.enabled'));
    }

    /** @test */
    public function a_user_can_be_reterived_from_provider()
    {
        $this->setupStormpathApplication();
        $this->createAccount(['login'=>'test@test.com', 'password'=>'superP4ss!']);

        $passwordGrant = new \Stormpath\Oauth\PasswordGrantRequest('test@test.com', 'superP4ss!');
        $auth = new \Stormpath\Oauth\PasswordGrantAuthenticator(app('stormpath.application'));
        $result =  $auth->authenticate($passwordGrant);

        $this->call('GET', 'login',[], $this->cookiesToSend($result));

        $user = app('stormpath.user');

        $this->assertNotNull($user);
        $this->assertEquals('test@test.com', $user->email);
    }

    /** @test */
    public function attempt_to_get_user_with_bad_access_token_returns_null()
    {
        $this->setupStormpathApplication();
        $this->createAccount(['login'=>'test@test.com', 'password'=>'superP4ss!']);

        $passwordGrant = new \Stormpath\Oauth\PasswordGrantRequest('test@test.com', 'superP4ss!');
        $auth = new \Stormpath\Oauth\PasswordGrantAuthenticator(app('stormpath.application'));
        $result =  $auth->authenticate($passwordGrant);

        $this->call('GET', 'login',[], $this->badCookiesToSend());

        $user = app('stormpath.user');

        $this->assertNull($user);
    }

    /** @test */
    public function attempt_to_get_user_with_no_access_token_returns_null()
    {
        $this->setupStormpathApplication();
        $this->createAccount(['login'=>'test@test.com', 'password'=>'superP4ss!']);

        $passwordGrant = new \Stormpath\Oauth\PasswordGrantRequest('test@test.com', 'superP4ss!');
        $auth = new \Stormpath\Oauth\PasswordGrantAuthenticator(app('stormpath.application'));
        $result =  $auth->authenticate($passwordGrant);

        $this->call('GET', 'login');

        $user = app('stormpath.user');

        $this->assertNull($user);
    }

    /** @test */
    public function it_will_refresh_the_access_token_if_expired()
    {
        $this->setupStormpathApplication();
        $this->createAccount(['login'=>'test@test.com', 'password'=>'superP4ss!']);

        $passwordGrant = new \Stormpath\Oauth\PasswordGrantRequest('test@test.com', 'superP4ss!');
        $auth = new \Stormpath\Oauth\PasswordGrantAuthenticator(app('stormpath.application'));
        $result =  $auth->authenticate($passwordGrant);

        $this->call('GET', 'login',[], $this->cookiesToSendRefreshOnly($result));

        $user = app('stormpath.user');

        $this->assertNotNull($user);

    }

    /**
     * @param Application $app
     *
     * @return StormpathLaravelServiceProvider
     */
    private function setupServiceProvider(Application $app)
    {
        // Create and register the provider.
        $provider = new StormpathLaravelServiceProvider($app);
        $app->register($provider);
        $provider->boot();
        return $provider;
    }

    private function cookiesToSend($result)
    {
        return [
            config('stormpath.web.accessTokenCookie.name') =>
                cookie(
                    config('stormpath.web.accessTokenCookie.name'),
                    $result->getAccessTokenString(),
                    $result->getExpiresIn(),
                    config('stormpath.web.accessTokenCookie.path'),
                    config('stormpath.web.accessTokenCookie.domain'),
                    config('stormpath.web.accessTokenCookie.secure'),
                    config('stormpath.web.accessTokenCookie.httpOnly')
                ),
            config('stormpath.web.refreshTokenCookie.name') =>
                cookie(
                    config('stormpath.web.refreshTokenCookie.name'),
                    $result->getRefreshTokenString(),
                    $result->getExpiresIn(),
                    config('stormpath.web.refreshTokenCookie.path'),
                    config('stormpath.web.refreshTokenCookie.domain'),
                    config('stormpath.web.refreshTokenCookie.secure'),
                    config('stormpath.web.refreshTokenCookie.httpOnly')
                )
        ];
    }

    private function badCookiesToSend()
    {
        return [
            config('stormpath.web.accessTokenCookie.name') =>
                cookie(
                    config('stormpath.web.accessTokenCookie.name'),
                    '123',
                    '3600',
                    config('stormpath.web.accessTokenCookie.path'),
                    config('stormpath.web.accessTokenCookie.domain'),
                    config('stormpath.web.accessTokenCookie.secure'),
                    config('stormpath.web.accessTokenCookie.httpOnly')
                ),
            config('stormpath.web.refreshTokenCookie.name') =>
                cookie(
                    config('stormpath.web.refreshTokenCookie.name'),
                    'abc',
                    '3600',
                    config('stormpath.web.refreshTokenCookie.path'),
                    config('stormpath.web.refreshTokenCookie.domain'),
                    config('stormpath.web.refreshTokenCookie.secure'),
                    config('stormpath.web.refreshTokenCookie.httpOnly')
                )
        ];
    }

    private function cookiesToSendRefreshOnly($result)
    {
        return [
            config('stormpath.web.refreshTokenCookie.name') =>
                cookie(
                    config('stormpath.web.refreshTokenCookie.name'),
                    $result->getRefreshTokenString(),
                    $result->getExpiresIn(),
                    config('stormpath.web.refreshTokenCookie.path'),
                    config('stormpath.web.refreshTokenCookie.domain'),
                    config('stormpath.web.refreshTokenCookie.secure'),
                    config('stormpath.web.refreshTokenCookie.httpOnly')
                )
        ];
    }

}
