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

class AuthenticateTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->app->router->get(
            'testAuthenticateMiddleware',
            [
                'middleware'=>'stormpath.auth',
                function() {
                    return 'Hello!';
                }
            ]
        );

    }

    /** @test */
    public function it_redirects_if_user_is_a_guest()
    {
        $this->get('testAuthenticateMiddleware');
        $this->assertRedirectedToRoute('stormpath.login');
    }

    /** @test */
    public function an_invalid_access_token_redirects_to_login_screen()
    {
        $this->setupStormpathApplication();
        $this->call('GET', 'testAuthenticateMiddleware',[],
            [
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
                        '123',
                        '3600',
                        config('stormpath.web.refreshTokenCookie.path'),
                        config('stormpath.web.refreshTokenCookie.domain'),
                        config('stormpath.web.refreshTokenCookie.secure'),
                        config('stormpath.web.refreshTokenCookie.httpOnly')
                    )
            ]);
        $this->assertRedirectedToRoute('stormpath.login');
    }

    /** @test */
    public function it_continues_if_user_is_authenticated()
    {
        $this->setupStormpathApplication();
        $this->createAccount(['login'=>'test@test.com', 'password'=>'superP4ss!']);

        $passwordGrant = new \Stormpath\Oauth\PasswordGrantRequest('test@test.com', 'superP4ss!');
        $auth = new \Stormpath\Oauth\PasswordGrantAuthenticator(app('stormpath.application'));
        $result =  $auth->authenticate($passwordGrant);

        $this->call('GET', 'testAuthenticateMiddleware',[], $this->cookiesToSend($result));
        $this->see('Hello!');


    }

    /** @test */
    public function it_will_refresh_token_if_old_token()
    {
        $this->setupStormpathApplication();
        $this->createAccount(['login'=>'test@test.com', 'password'=>'superP4ss!']);

        $passwordGrant = new \Stormpath\Oauth\PasswordGrantRequest('test@test.com', 'superP4ss!');
        $auth = new \Stormpath\Oauth\PasswordGrantAuthenticator(app('stormpath.application'));
        $result =  $auth->authenticate($passwordGrant);

        $this->call('GET', 'testAuthenticateMiddleware',[],
            [
                config('stormpath.web.accessTokenCookie.name') =>
                    cookie(
                        config('stormpath.web.accessTokenCookie.name'),
                        'eyJraWQiOiIxUE4zRlhJMFU3OUUyTUhDRjZYVVlHVTRaIiwiYWxnIjoiSFMyNTYifQ.eyJqdGkiOiJ5VnZ4ZTV4T1NqOHl6WHNWa0w4VmIiLCJpYXQiOjE0NDk3ODU5ODgsImlzcyI6Imh0dHBzOi8vYXBpLnN0b3JtcGF0aC5jb20vdjEvYXBwbGljYXRpb25zL3hSQ1FsNmRIRFl2UWtPMzZDY2EwSSIsInN1YiI6Imh0dHBzOi8vYXBpLnN0b3JtcGF0aC5jb20vdjEvYWNjb3VudHMveGloYzVpYXlwb1BvaVFsakFEU2tXIiwiZXhwIjoxNDQ5Nzg5NTg4LCJydGkiOiJ5VnZ4YWxzVHNRQU1BUzFKVVRydFgifQ.gDO2pfxTfItjW8YMM_ZKf8BvqU3kenR0g8my7mneAd8',
                        time()-86400,
                        config('stormpath.web.accessTokenCookie.path'),
                        config('stormpath.web.accessTokenCookie.domain'),
                        config('stormpath.web.accessTokenCookie.secure'),
                        config('stormpath.web.accessTokenCookie.httpOnly')
                    ),
                config('stormpath.web.refreshTokenCookie.name') =>
                    cookie(
                        config('stormpath.web.refreshTokenCookie.name'),
                        $result->getRefreshTokenString(),
                        time()-86400,
                        config('stormpath.web.refreshTokenCookie.path'),
                        config('stormpath.web.refreshTokenCookie.domain'),
                        config('stormpath.web.refreshTokenCookie.secure'),
                        config('stormpath.web.refreshTokenCookie.httpOnly')
                    )
            ]);

        $this->see('Hello!');
    }

    /** @test */
    public function it_will_redirect_to_login_if_old_token_and_can_not_refresh()
    {
        $this->setupStormpathApplication();
        $this->createAccount(['login'=>'test@test.com', 'password'=>'superP4ss!']);

        $passwordGrant = new \Stormpath\Oauth\PasswordGrantRequest('test@test.com', 'superP4ss!');
        $auth = new \Stormpath\Oauth\PasswordGrantAuthenticator(app('stormpath.application'));
        $result =  $auth->authenticate($passwordGrant);

        $this->call('GET', 'testAuthenticateMiddleware',[],
            [
                config('stormpath.web.accessTokenCookie.name') =>
                    cookie(
                        config('stormpath.web.accessTokenCookie.name'),
                        'eyJraWQiOiIxUE4zRlhJMFU3OUUyTUhDRjZYVVlHVTRaIiwiYWxnIjoiSFMyNTYifQ.eyJqdGkiOiJ5VnZ4ZTV4T1NqOHl6WHNWa0w4VmIiLCJpYXQiOjE0NDk3ODU5ODgsImlzcyI6Imh0dHBzOi8vYXBpLnN0b3JtcGF0aC5jb20vdjEvYXBwbGljYXRpb25zL3hSQ1FsNmRIRFl2UWtPMzZDY2EwSSIsInN1YiI6Imh0dHBzOi8vYXBpLnN0b3JtcGF0aC5jb20vdjEvYWNjb3VudHMveGloYzVpYXlwb1BvaVFsakFEU2tXIiwiZXhwIjoxNDQ5Nzg5NTg4LCJydGkiOiJ5VnZ4YWxzVHNRQU1BUzFKVVRydFgifQ.gDO2pfxTfItjW8YMM_ZKf8BvqU3kenR0g8my7mneAd8',
                        time()-86400,
                        config('stormpath.web.accessTokenCookie.path'),
                        config('stormpath.web.accessTokenCookie.domain'),
                        config('stormpath.web.accessTokenCookie.secure'),
                        config('stormpath.web.accessTokenCookie.httpOnly')
                    ),
                config('stormpath.web.refreshTokenCookie.name') =>
                    cookie(
                        config('stormpath.web.refreshTokenCookie.name'),
                        $result->getAccessTokenString(),
                        time()-86400,
                        config('stormpath.web.refreshTokenCookie.path'),
                        config('stormpath.web.refreshTokenCookie.domain'),
                        config('stormpath.web.refreshTokenCookie.secure'),
                        config('stormpath.web.refreshTokenCookie.httpOnly')
                    )
            ]);

        $this->assertRedirectedToRoute('stormpath.login');
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






}
