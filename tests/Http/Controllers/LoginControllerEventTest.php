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

namespace Stormpath\Tests\Http\Controllers;

use Stormpath\Laravel\Tests\TestCase;

class LoginControllerEventTest extends TestCase
{

    /** @test */
    public function it_fires_the_UserIsLoggingIn_event_before_authentication()
    {
        $this->expectsEvents(\Stormpath\Laravel\Events\UserIsLoggingIn::class);

        $this->setupStormpathApplication();
        $account = $this->createAccount(['login' => 'test@test.com', 'password' => 'superP4ss!']);
        $this->post('login', ['login' => 'test@test.com', 'password' => 'superP4ss!']);

        $this->seeCookie(config('stormpath.web.accessTokenCookie.name'));
        $this->seeCookie(config('stormpath.web.refreshTokenCookie.name'));
        $account->delete();
    }

    /**
     * @test
     * @expectedException \Stormpath\Laravel\Exceptions\ActionAbortedException
    */
    public function it_aborts_the_login_if_the_UserIsLoggingIn_event_listener_returns_false()
    {
        \Event::listen(\Stormpath\Laravel\Events\UserIsLoggingIn::class, function ($event) {
            return false;
        });

        $this->setupStormpathApplication();
        $account = $this->createAccount(['login' => 'test@test.com', 'password' => 'superP4ss!']);
        $this->post('login', ['login' => 'test@test.com', 'password' => 'superP4ss!']);

        $this->seeCookie(config('stormpath.web.accessTokenCookie.name'));
        $this->seeCookie(config('stormpath.web.refreshTokenCookie.name'));
        $account->delete();
    }

    /** @test */
    public function it_fires_the_UserHasLoggedIn_event_after_authentication()
    {
        $this->expectsEvents(\Stormpath\Laravel\Events\UserHasLoggedIn::class);

        $this->setupStormpathApplication();
        $account = $this->createAccount(['login' => 'test@test.com', 'password' => 'superP4ss!']);
        $this->post('login', ['login' => 'test@test.com', 'password' => 'superP4ss!']);

        $this->seeCookie(config('stormpath.web.accessTokenCookie.name'));
        $this->seeCookie(config('stormpath.web.refreshTokenCookie.name'));
        $account->delete();
    }

}
