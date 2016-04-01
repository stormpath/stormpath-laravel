<?php
/*
 * Copyright 2016 Stormpath, Inc.
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

namespace Stormpath\Laravel\Tests\Http\Controllers;

use Illuminate\Http\Request;
use Stormpath\Laravel\Tests\TestCase;

class OauthControllerTest extends TestCase
{
    public function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);
    }

    /** @test */
    public function it_can_successfully_do_a_password_grant_type()
    {
        $this->setupStormpathApplication();
        $account = $this->createAccount(['login' => 'test@test.com', 'password' => 'superP4ss!']);

        $this->json('post', config('stormpath.web.oauth2.uri'), [
            'username' => 'test@test.com',
            'password' => 'superP4ss!',
            'grant_type' => 'password'
        ]);

        $this->seeJson();
        $this->assertResponseOk();
        $response = $this->decodeResponseJson();

        $this->assertArrayHasKey('access_token', $response);
        $this->assertArrayHasKey('refresh_token', $response);
        $this->assertArrayHasKey('expires_in', $response);
        $this->assertArrayHasKey('token_type', $response);

        $account->delete();
    }

    /** @test */
    public function it_will_return_error_on_unsuccessful_password_grant_type()
    {
        $this->setupStormpathApplication();
        $account = $this->createAccount(['login' => 'test@test.com', 'password' => 'superP4ss!']);

        $this->json('post', config('stormpath.web.oauth2.uri'), [
            'username' => 'test2@test.com',
            'password' => 'superP4ss!',
            'grant_type' => 'password'
        ]);

        $this->seeJson([
            "message" => "Invalid username or password.",
            "error" => "invalid_grant"
        ]);
        $this->assertResponseStatus(400);
        $account->delete();
    }


    /** @test */
    public function it_can_successfully_do_a_refresh_grant_type()
    {
        $this->setupStormpathApplication();
        $account = $this->createAccount(['login' => 'test@test.com', 'password' => 'superP4ss!']);

        $this->json('post', config('stormpath.web.oauth2.uri'), [
            'username' => 'test@test.com',
            'password' => 'superP4ss!',
            'grant_type' => 'password'
        ]);

        $this->seeJson();
        $this->assertResponseOk();
        $response = $this->decodeResponseJson();
        $refresh = $response['refresh_token'];
        $access = $response['access_token'];

        $this->json('post', config('stormpath.web.oauth2.uri'), [
            'refresh_token' => $refresh,
            'grant_type' => 'refresh_token'
        ]);

        $response2 = $this->decodeResponseJson();

        $this->assertArrayHasKey('access_token', $response2);
        $this->assertArrayHasKey('refresh_token',$response2);
        $this->assertArrayHasKey('expires_in', $response2);
        $this->assertArrayHasKey('token_type', $response2);

        $this->assertNotEquals($access, $response2['access_token']);
        $account->delete();
    }

    /** @test */
    public function a_bad_refresh_token_will_respond_with_error()
    {
        $this->setupStormpathApplication();

        $this->json('post', config('stormpath.web.oauth2.uri'), [
            'refresh_token' => '123',
            'grant_type' => 'refresh_token'
        ]);

        $this->seeJson([
            "message" => "Token is invalid",
            "error" => "invalid_grant"
        ]);

        $this->seeStatusCode(400);
    }

    /** @test */
    public function no_refresh_token_with_refresh_grant_type_will_respond_with_error()
    {
        $this->setupStormpathApplication();

        $this->json('post', config('stormpath.web.oauth2.uri'), [
            'grant_type' => 'refresh_token'
        ]);

        $this->seeJson([
            "message" => "The refresh_token parameter is required.",
            "error" => "invalid_request"
        ]);

        $this->seeStatusCode(400);
    }

    /** @test */
    public function an_unsupported_grant_type_returns_error()
    {
        $this->setupStormpathApplication();

        $this->json('post', config('stormpath.web.oauth2.uri'), [
            'grant_type'=>'foobar'
        ]);

        $this->seeJson([
            'message' => 'The authorization grant type is not supported by the authorization server.',
            'error' => 'unsupported_grant_type'
        ]);
    }

}
