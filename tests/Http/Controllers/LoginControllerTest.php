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

class LoginControllerTest extends TestCase
{
    /** @test */
    public function it_requires_a_login_to_be_submitted()
    {
        $this->post('login', ['password'=>'superPassword']);
        $this->assertRedirectedTo(config('stormpath.web.login.uri'));
        $this->assertSessionHasErrors(['login'=>'Login is required.']);
        $this->assertHasOldInput();
        $this->followRedirects();
        $this->seePageIs('login');
        $this->see('Log In');

    }

    /** @test */
    public function it_requires_a_login_to_be_submitted_json()
    {
        $this->json('post', 'login', ['password'=>'superPassword']);
        $this->seeJson(['message'=>'Login is required.','status'=>400]);
        $this->seeStatusCode(400);


    }

    /** @test */
    public function it_requires_a_password_to_be_submitted()
    {
        $this->post('login', ['login' => 'someLogin']);
        $this->assertRedirectedTo(config('stormpath.web.login.uri'));
        $this->assertSessionHasErrors(['password' => 'Password is required.']);
        $this->assertHasOldInput();
        $this->followRedirects();
        $this->seePageIs('login');
        $this->see('Log In');


    }

    /** @test */
    public function successful_login_sets_session_variables()
    {
        $this->setupStormpathApplication();
        $account = $this->createAccount(['login' => 'test@test.com', 'password' => 'superP4ss!']);
        $this->post('login', ['login' => 'test@test.com', 'password' => 'superP4ss!']);

        $this->assertTrue(cookie()->hasQueued(config('stormpath.web.accessTokenCookie.name')));
        $this->assertTrue(cookie()->hasQueued(config('stormpath.web.refreshTokenCookie.name')));
        $account->delete();
    }

    /** @test */
    public function will_display_error_if_account_is_invalid()
    {
        $this->setupStormpathApplication();
        $this->post('login', ['login' => 'somelogin', 'password' => 'somePassword']);
        $this->assertRedirectedTo(config('stormpath.web.login.uri'));
        $this->assertSessionHasErrors(['errors'=>'Invalid username or password.']);
        $this->assertHasOldInput();
        $this->followRedirects();
        $this->seePageIs('login');
        $this->see('Log In');

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
        $this->setupStormpathApplication();
        $account = $this->createAccount(['login' => 'test@test.com', 'password' => 'superP4ss!']);
        $this->visit('login')
            ->fillForm('Log In',['login' => 'test@test.com', 'password' => 'superP4ss!']);


        $this->call('POST', config('stormpath.web.logout.uri'));

        $headers = $this->response->headers;
        $cookies = $headers->getCookies();
        foreach($cookies as $cookie) {
            if($cookie->getName() == config('stormpath.web.accessTokenCookie.name') || $cookie->getName() == config('stormpath.web.refreshTokenCookie.name')) {
                $this->assertLessThan(time(), $cookie->getExpiresTime());
            }
        }

        $this->assertRedirectedTo(config('stormpath.web.logout.nextUri'));
        $account->delete();
    }

    /** @test */
    public function request_to_login_with_json_accept_returns_json_response()
    {
        $this->setupStormpathApplication();

        $this->json('get', config('stormpath.web.login.uri'))
            ->seeJson();

        $this->see('csrf');
        $this->see('login');
        $this->see('password');
        $this->see('accountStores');
        $this->assertResponseOk();

    }

    /** @test */
    public function posting_to_login_with_json_returns_account_object_as_json()
    {
        $this->setupStormpathApplication();
        $account = $this->createAccount(['login' => 'test@test.com', 'password' => 'superP4ss!']);

        $this->json(
            'post',
            config('stormpath.web.login.uri'),
            [
                '_token' => csrf_field(),
                'login' => 'test@test.com',
                'password' => 'superP4ss!'
            ]
        )
            ->seeJson();

        $this->dontSee('errors');
        $this->see('account');
        $this->see($account->username);
        $this->assertResponseOk();


        $account->delete();
    }

    /** @test */
    public function posting_to_login_with_json_returns_account_object_and_expand_as_json()
    {
        $this->setupStormpathApplication();
        $account = $this->createAccount(['login' => 'test@test.com', 'password' => 'superP4ss!']);

        config(['stormpath.web.me.expand.applications'=>true]);

        $this->json(
            'post',
            config('stormpath.web.login.uri'),
            [
                '_token' => csrf_field(),
                'login' => 'test@test.com',
                'password' => 'superP4ss!'
            ]
        )
            ->seeJson();

        $this->dontSee('errors');
        $this->see('account');
        $this->see('applications');
        $this->see($account->username);
        $this->assertResponseOk();


        $account->delete();
    }

    /** @test */
    public function posting_to_login_with_json_with_failed_login_returns_json_error()
    {
        $this->setupStormpathApplication();
        $account = $this->createAccount(['login' => 'test@test.com', 'password' => 'superP4ss!']);

        $this->json(
            'post',
            config('stormpath.web.login.uri'),
            [
                '_token' => csrf_field(),
                'login' => 'test!@test.com',
                'password' => 'superP4ss!'
            ]
        )
            ->seeJson();

        $this->see('message');
        $this->see('status');
        $this->dontSee('account');

        $this->assertResponseStatus(400);
        $account->delete();
    }


    /** @test */
    public function it_can_logout_of_the_system_json()
    {
        $this->setupStormpathApplication();
        $account = $this->createAccount(['login' => 'test@test.com', 'password' => 'superP4ss!']);
        $this->visit('login')
            ->fillForm('Log In',['login' => 'test@test.com', 'password' => 'superP4ss!']);


        $this->json('POST', config('stormpath.web.logout.uri'));

        $this->seeStatusCode(200);

        $headers = $this->response->headers;
        $cookies = $headers->getCookies();
        foreach($cookies as $cookie) {
            if($cookie->getName() == config('stormpath.web.accessTokenCookie.name') || $cookie->getName() == config('stormpath.web.refreshTokenCookie.name')) {
                $this->assertLessThan(time(), $cookie->getExpiresTime());
            }
        }

        $account->delete();
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Please use the standard login/password method instead
     */
    public function social_login_attempt_with_stormpath_throws_exception()
    {
        $this->setupStormpathApplication();

        $this->json('POST', config('stormpath.web.login.uri'), [
            "providerData" => [
                "providerId" => 'stormpath'
            ]
        ]);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The social provider foobar is not supported
     */
    public function social_login_attempt_with_foobar_throws_exception()
    {
        $this->setupStormpathApplication();

        $this->json('POST', config('stormpath.web.login.uri'), [
            "providerData" => [
                "providerId" => 'foobar'
            ]
        ]);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Either code or accessToken must be set for FacebookProviderAccountRequest
     */
    public function social_login_attempt_with_facebook_throws_exception()
    {
        $this->setupStormpathApplication();

        $this->json('POST', config('stormpath.web.login.uri'), [
            "providerData" => [
                "providerId" => 'facebook',
                "accessToken" => 'willCauseError'
            ]
        ]);
    }




}
