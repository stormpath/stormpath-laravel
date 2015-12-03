<?php

namespace Stormpath\Laravel\Tests\Support;

use Illuminate\Config\Repository;
use Illuminate\Foundation\Application;
use Stormpath\Laravel\Support\StormpathLaravelServiceProvider;
use Stormpath\Laravel\Tests\TestCase;

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
