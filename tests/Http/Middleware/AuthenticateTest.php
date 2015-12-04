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
    public function it_continues_if_user_is_authenticated()
    {
        $this->setupStormpathApplication();
        config(['stormpath.web.register.autoAuthorize.enabled' => true]);
        $account = $this->createAccount(['username'=>'testUsername', 'email' => 'test@account.com', 'password' => 'superP4ss!']);

        $this->post('login', ['login' => 'test@account.com', 'password' => 'superP4ss!']);

        $this->get('testAuthenticateMiddleware');
        $this->see('Hello!');
    }


    /** @test */
    public function an_invalid_access_token_redirects_to_login_screen()
    {
        $this->setupStormpathApplication();
        session([config('stormpath.web.accessTokenCookie.name') => '123']);

        $this->get('testAuthenticateMiddleware');
        $this->assertRedirectedToRoute('stormpath.login');
    }

}
