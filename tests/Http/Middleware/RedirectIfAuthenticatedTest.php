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

namespace Stormpath\Laravel\Tests\Http\Middleware;

use Stormpath\Laravel\Tests\TestCase;

class RedirectIfAuthenticatedTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->app->router->get(
            '/',
            function() {
                return 'Home';
            }
        );

        $this->app->router->get(
            'testRedirectIfAuthenticatedMiddleware',
            [
                'middleware'=>'stormpath.guest',
                function() {
                    return 'Hello!';
                }
            ]
        );
    }

    /** @test */
    public function it_coninutes_if_user_is_a_guest()
    {
        $this->get('testRedirectIfAuthenticatedMiddleware');
        $this->see('Hello!');
    }

    /** @test */
    public function it_redirects_home_if_user_is_authenticated()
    {
        $this->setupStormpathApplication();
        $this->createAccount(['login'=>'test@test.com', 'password'=>'superP4ss!']);

        $passwordGrant = new \Stormpath\Oauth\PasswordGrantRequest('test@test.com', 'superP4ss!');
        $auth = new \Stormpath\Oauth\PasswordGrantAuthenticator(app('stormpath.application'));
        $result =  $auth->authenticate($passwordGrant);

        $this->call('GET', 'testRedirectIfAuthenticatedMiddleware',[], $this->cookiesToSend($result));
        $this->assertRedirectedTo('/');
        $this->followRedirects();
        $this->see('Home');
    }

    /** @test */
    public function it_will_refresh_the_access_token_if_expired()
    {
        $this->setupStormpathApplication();
        $this->createAccount(['login'=>'test@test.com', 'password'=>'superP4ss!']);

        $passwordGrant = new \Stormpath\Oauth\PasswordGrantRequest('test@test.com', 'superP4ss!');
        $auth = new \Stormpath\Oauth\PasswordGrantAuthenticator(app('stormpath.application'));
        $result =  $auth->authenticate($passwordGrant);

        $this->call('GET', 'testRedirectIfAuthenticatedMiddleware',[], $this->cookiesToSendRefreshOnly($result));
        $this->assertRedirectedTo('/');
        $this->followRedirects();
        $this->see('Home');


    }

    /** @test */
    public function it_will_not_try_to_refresh_access_token_if_json_is_requested()
    {
        $this->setupStormpathApplication();
        $this->createAccount(['login'=>'test@test.com', 'password'=>'superP4ss!']);

        $passwordGrant = new \Stormpath\Oauth\PasswordGrantRequest('test@test.com', 'superP4ss!');
        $auth = new \Stormpath\Oauth\PasswordGrantAuthenticator(app('stormpath.application'));
        $result =  $auth->authenticate($passwordGrant);

        $this->json('GET', 'testRedirectIfAuthenticatedMiddleware',[], $this->cookiesToSendRefreshOnly($result));

        $this->seeStatusCode(401);
    }


    /** @test */
    public function it_will_return_null_if_no_access_token_and_invalid_refresh_token()
    {
        $this->setupStormpathApplication();
        $this->createAccount(['login'=>'test@test.com', 'password'=>'superP4ss!']);

        $passwordGrant = new \Stormpath\Oauth\PasswordGrantRequest('test@test.com', 'superP4ss!');
        $auth = new \Stormpath\Oauth\PasswordGrantAuthenticator(app('stormpath.application'));
        $result =  $auth->authenticate($passwordGrant);

        $this->get('testRedirectIfAuthenticatedMiddleware');
        $this->see('Hello!');


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
