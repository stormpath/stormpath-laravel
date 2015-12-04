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

namespace Stormpath\Laravel\Tests\Support;

use Illuminate\Config\Repository;
use Illuminate\Foundation\Application;
use Stormpath\Laravel\Support\StormpathLaravelServiceProvider;
use Stormpath\Laravel\Tests\TestCase;
use Stormpath\Stormpath;

class StormpathLaravelServiceProviderTest extends TestCase
{
    /** @test */
    public function it_tells_us_what_it_provides()
    {
        $provider = $this->setupServiceProvider($this->app);
        $provides = $provider->provides();

        $this->assertContains('stormpath.client', $provides);
        $this->assertContains('stormpath.application', $provides);
    }

    /** @test */
    public function it_provides_an_instance_of_client()
    {
        $client = app('stormpath.client');

        $this->assertInstanceOf('\Stormpath\Client', $client);

    }

    /** @test */
    public function it_provides_an_instance_of_application()
    {
        $this->setupStormpathApplication();
        $application = app('stormpath.application');

        $this->assertInstanceOf('\Stormpath\Resource\Application', $application);
    }

    /** @test */
    public function it_returns_null_from_stormpath_application_if_application_does_not_exist()
    {
        $application = app('stormpath.application');

        $this->assertNull($application);
        $this->assertArrayNotHasKey('enabled', config('stormpath.web.verifyEmail'));
    }

    /** @test */
    public function it_sets_verify_email_config_to_false_by_default()
    {
        $this->setupStormpathApplication();
        app('stormpath.application');
        $this->assertArrayHasKey('enabled', config('stormpath.web.verifyEmail'));
        $this->assertFalse(config('stormpath.web.verifyEmail.enabled'));
    }

    /** @test */
    public function it_sets_verify_email_config_to_true_if_account_store_mapping_for_application_is_set_to_verify_email()
    {
        $this->setupStormpathApplication();
        $accountStoreMappings = $this->application->accountStoreMappings;

        if ($accountStoreMappings) {
            foreach ($accountStoreMappings as $asm) {
                $directory = $asm->accountStore;
                $acp = $directory->accountCreationPolicy;
                $acp->verificationEmailStatus = Stormpath::ENABLED;
                $acp->save();
            }
        }

        $application = app('stormpath.application');

        $this->assertTrue(config('stormpath.web.verifyEmail.enabled'));
    }



    /**
     * @param Application $app
     *
     * @return StormpathLaravelServiceProvider
     */
    private function setupServiceProvider(Application $app)
    {
        // Create and register the provider.
        $provider = new StormpathLaravelServiceProvider($app);
        $app->register($provider);
        $provider->boot();
        return $provider;
    }
}
