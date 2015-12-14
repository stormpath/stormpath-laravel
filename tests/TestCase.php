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

namespace Stormpath\Laravel\Tests;

use Mockery as m;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{

    protected $application;

    public function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app->make('Illuminate\Contracts\Http\Kernel')->pushMiddleware('Illuminate\Session\Middleware\StartSession');

    }


    public function setupStormpathApplication()
    {
        $this->application = \Stormpath\Resource\Application::instantiate(array('name' => 'Test Application  - ' . microtime(), 'description' => 'Description of Main App', 'status' => 'enabled'));
        self::createResource(\Stormpath\Resource\Application::PATH, $this->application, array('createDirectory' => true));

        config(['stormpath.application.href'=>$this->application->href]);
    }

    public function createAccount($overrides = [])
    {
        $account = \Stormpath\Resource\Account::instantiate(array_merge([
            'givenName' => 'Test',
            'surname' => 'Account',
            'email' => 'test@test.com',
            'password' => 'superP4ss!'
        ], $overrides));
        $account = $this->application->createAccount($account);
        return $account;
    }

    protected function getPackageProviders($app)
    {
        return ['Stormpath\Laravel\Support\StormpathLaravelServiceProvider'];
    }

    protected static function createResource($parentHref, \Stormpath\Resource\Resource $resource, array $options = array())
    {

        if (!(strpos($parentHref, '/') === 0))
        {
            $parentHref = '/' . $parentHref;
        }

        $client = app('stormpath.client');
        $resource = $client->dataStore->create($parentHref, $resource, get_class($resource), $options);
        return $resource;
    }

    public function tearDown()
    {
        if ($this->application)
        {
            $accountStoreMappings = $this->application->accountStoreMappings;

            if ($accountStoreMappings)
            {
                foreach($accountStoreMappings as $asm)
                {
                    $accountStore = $asm->accountStore;
                    $asm->delete();
                    $accountStore->delete();
                }
            }

            $this->application->delete();
            $this->application = null;
        }

        parent::tearDown();
    }

    /**
     * Asserts that the response does not contain the given cookie.
     *
     * @param  string $cookieName
     * @return $this
     */
    protected function seeNotCookie($cookieName)
    {
        $headers = $this->response->headers;
        $exist = false;

        foreach ($headers->getCookies() as $cookie) {
            if ($cookie->getName() === $cookieName) {
                $exist = true;
                break;
            }
        }

        $this->assertFalse($exist, "Cookie [{$cookieName}] present on response.");

        return $this;
    }
}