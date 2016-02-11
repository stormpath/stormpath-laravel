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

namespace Stormpath\Tests\Http\Controllers;

use Illuminate\Http\Request;
use Stormpath\Laravel\Http\Controllers\SocialCallbackController;
use Stormpath\Laravel\Tests\TestCase;

class SocialCallbackControllerTest extends TestCase
{
    public function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

    }

    /** @test */
    public function facebook_callback_will_set_tokens()
    {
        $this->setupStormpathApplication();
        $account = $this->createAccount();
        $accountObject = new \stdClass();
        $accountObject->account = $account;
        $cookieJar = app('cookie');

        $application = \Mockery::mock('Stormpath\Resource\Application');
        $controller = new SocialCallbackController($application);

        $application->shouldReceive('getAccount')->once()->andReturn($accountObject);

        $request = new Request();
        $request->get('access_token', '123');

        $response = $controller->facebook($request);

        $this->assertInstanceOf('Illuminate\Http\RedirectResponse', $response);
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertTrue($response->headers->contains('location', url(config('stormpath.web.login.nextUri'))));

        $this->assertTrue($cookieJar->hasQueued('access_token'));
        $this->assertTrue($cookieJar->hasQueued('refresh_token'));

        $account->delete();
        $cookieJar->unqueue('access_token');
        $cookieJar->unqueue('refresh_token');

        \Mockery::close();

    }

    /** @test */
    public function google_callback_will_set_tokens()
    {
        $this->setupStormpathApplication();
        $account = $this->createAccount();
        $accountObject = new \stdClass();
        $accountObject->account = $account;
        $cookieJar = app('cookie');

        $application = \Mockery::mock('Stormpath\Resource\Application');
        $controller = new SocialCallbackController($application);

        $application->shouldReceive('getAccount')->once()->andReturn($accountObject);

        $request = new Request();
        $request->get('code', '123');

        $response = $controller->google($request);

        $this->assertInstanceOf('Illuminate\Http\RedirectResponse', $response);
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertTrue($response->headers->contains('location', url(config('stormpath.web.login.nextUri'))));

        $this->assertTrue($cookieJar->hasQueued('access_token'));
        $this->assertTrue($cookieJar->hasQueued('refresh_token'));

        $account->delete();
        $cookieJar->unqueue('access_token');
        $cookieJar->unqueue('refresh_token');

        \Mockery::close();
    }

    /** @test */
    public function linkedin_callback_will_set_tokens()
    {
        $this->setupStormpathApplication();
        $account = $this->createAccount();
        $accountObject = new \stdClass();
        $accountObject->account = $account;
        $cookieJar = app('cookie');

        $application = \Mockery::mock('Stormpath\Resource\Application');
        $controller = new SocialCallbackController($application);

        $application->shouldReceive('getAccount')->once()->andReturn($accountObject);

        $request = new Request();
        $request->get('code', '123');

        $response = $controller->linkedin($request);

        $this->assertInstanceOf('Illuminate\Http\RedirectResponse', $response);
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertTrue($response->headers->contains('location', url(config('stormpath.web.login.nextUri'))));

        $this->assertTrue($cookieJar->hasQueued('access_token'));
        $this->assertTrue($cookieJar->hasQueued('refresh_token'));

        $account->delete();
        $cookieJar->unqueue('access_token');
        $cookieJar->unqueue('refresh_token');

        \Mockery::close();
    }

}
