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
        session([config('stormpath.web.accessTokenCookie.name') => '123']);
        session([config('stormpath.web.refreshTokenCookie.name') => '123']);

        $this->get('testRedirectIfAuthenticatedMiddleware');
        $this->assertRedirectedTo('/');
        $this->followRedirects();
        $this->see('Home');
    }




}
