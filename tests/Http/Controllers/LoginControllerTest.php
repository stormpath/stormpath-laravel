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

use Mockery as m;
use Stormpath\Laravel\Tests\TestCase;

class LoginControllerTest extends TestCase
{
    /** @test */
    public function it_requires_a_login_to_be_submitted()
    {
        $this->post('login', ['password'=>'superPassword']);
        $this->assertRedirectedTo(config('stormpath.web.login.uri'));
        $this->followRedirects();
        $this->seePageIs('login');
        $this->see('Log In');

        $this->assertSessionHasErrors(['login'=>'Login is required.']);
        $this->assertHasOldInput();
    }

    /** @test */
    public function it_requires_a_password_to_be_submitted()
    {
        $this->post('login', ['login' => 'someLogin']);
        $this->assertRedirectedTo(config('stormpath.web.login.uri'));
        $this->followRedirects();
        $this->seePageIs('login');
        $this->see('Log In');

        $this->assertSessionHasErrors(['password' => 'Password is required.']);
        $this->assertHasOldInput();

    }

    /** @test */
    public function successful_login_sets_session_variables()
    {
        $this->setupStormpathApplication();
        $account = $this->createAccount(['login' => 'test@test.com', 'password' => 'superP4ss!']);
        $this->post('login', ['login' => 'test@test.com', 'password' => 'superP4ss!']);
        $this->assertSessionHas(config('stormpath.web.accessTokenCookie.name'));
        $this->assertSessionHas(config('stormpath.web.refreshTokenCookie.name'));
        $account->delete();
    }

    /** @test */
    public function will_display_error_if_account_is_invalid()
    {
        $this->setupStormpathApplication();
        $this->post('login', ['login' => 'somelogin', 'password' => 'somePassword']);
        $this->assertRedirectedTo(config('stormpath.web.login.uri'));
        $this->followRedirects();
        $this->seePageIs('login');
        $this->see('Log In');

        $this->assertSessionHasErrors(['errors'=>'Invalid username or password.']);
        $this->assertHasOldInput();
    }

    /** @test */
    public function will_redirect_to_next_uri_on_login()
    {
        $this->setupStormpathApplication();
        $account = $this->createAccount(['login' => 'test@test.com', 'password' => 'superP4ss!']);
        $this->post('login', ['login' => 'test@test.com', 'password' => 'superP4ss!']);
        $this->assertRedirectedTo(config('stormpath.web.login.nextUri'));
        $account->delete();

    }

    /** @test */
    public function it_can_logout_of_the_system()
    {
        session([config('stormpath.web.accessTokenCookie.name') => '123']);
        session([config('stormpath.web.refreshTokenCookie.name') => '123']);

        $this->assertSessionHas(config('stormpath.web.accessTokenCookie.name'));
        $this->assertSessionHas(config('stormpath.web.refreshTokenCookie.name'));

        $this->get(config('stormpath.web.logout.uri'));

        $this->assertNull(session(config('stormpath.web.accessTokenCookie.name')));
        $this->assertNull(session(config('stormpath.web.refreshTokenCookie.name')));
        
        $this->assertRedirectedTo(config('stormpath.web.logout.nextUri'));
    }
}
