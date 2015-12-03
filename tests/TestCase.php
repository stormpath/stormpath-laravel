<?php

namespace Stormpath\Laravel\Tests;

use Mockery as m;

class TestCase extends \Orchestra\Testbench\TestCase
{

    private $application;

    public function setupStormpathApplication()
    {
        $this->application = \Stormpath\Resource\Application::instantiate(array('name' => 'Test Application  - ' . microtime(), 'description' => 'Description of Main App', 'status' => 'enabled'));
        self::createResource(\Stormpath\Resource\Application::PATH, $this->application, array('createDirectory' => true));
        $href = $this->application->href;
        $href = explode('/',$href);
        config(['stormpath.application'=>end($href)]);
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
}