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

class ProducesTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->app->router->get(
            'testProducesMiddleware',
            [
                'middleware'=>'stormpath.produces',
                function() {
                    return 'Hello!';
                }
            ]
        );

    }

    /** @test */
    public function request_returns_page_when_produces_array_allows()
    {
        config(['stormpath.web.produces'=>['text/html']]);
        $this->get('testProducesMiddleware')
            ->see('Hello!');
    }

    /** @test */
    public function json_request_returns_406_when_produces_array_does_not_contain_application_json()
    {
        config(['stormpath.web.produces'=>['text/html']]);
        $this->json('GET', 'testProducesMiddleware')
            ->see('Accept Header is not allowed.')
            ->assertResponseStatus(406);
    }

    /** @test */
    public function a_406_is_returned_when_nothing_is_in_produces_config()
    {
        config(['stormpath.web.produces'=>[]]);
        $this->get('testProducesMiddleware')
            ->see('The system does not know how to respond to any accept headers defined.')
            ->assertResponseStatus(406);
    }


}
