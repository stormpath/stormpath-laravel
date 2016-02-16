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

namespace Stormpath\Tests\Http;

use Stormpath\Laravel\Tests\TestCase;

class RoutesTest extends TestCase
{
    public function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        config(['stormpath.web.socialProviders.enabled'=>true]);
    }

    public function setUp()
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $this->setupStormpathApplication();
    }

    /** @test */
    public function social_callback_routes_enabled()
    {
        $this->get('callbacks/google?code=123')->assertRedirectedTo(config('stormpath.web.login.uri'));
        $this->get('callbacks/facebook?access_token=123')->assertRedirectedTo(config('stormpath.web.login.uri'));
//        $this->get('callbacks/linkedin?code=123')->assertRedirectedTo(config('stormpath.web.login.uri'));
    }

}
