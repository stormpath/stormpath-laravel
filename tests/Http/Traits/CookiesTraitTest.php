<?php

namespace Stormpath\Tests\Http\Traits;

use Stormpath\Laravel\Http\Traits\AuthenticatesUser;
use Stormpath\Laravel\Http\Traits\Cookies;
use Stormpath\Laravel\Tests\TestCase;

class CookiesTraitTest extends TestCase
{
    private $stub;

    public function setUp()
    {
        parent::setUp();
        $this->stub = new CookiesTraitStub();

    }

    /** @test */
    public function an_access_token_should_be_queued()
    {
        $this->setupStormpathApplication();
        $account = $this->createAccount(['email'=>'test@domain.com', 'password'=>'superP4ss!']);

        $result = $this->stub->authenticate('test@domain.com', 'superP4ss!');
        $accessToken = $result->getAccessTokenString();

        $this->stub->queueAccessToken($accessToken);

        $cookieJar = app('cookie');

        $this->assertTrue($cookieJar->hasQueued(config('stormpath.web.accessTokenCookie.name')));
        $queuedCookie = $cookieJar->getQueuedCookies()['access_token'];

        $this->assertEquals(time()+3600, $queuedCookie->getExpiresTime());

        $cookieJar->unqueue('access_token');
    }

    /** @test */
    public function a_refresh_token_should_be_queued()
    {
        $this->setupStormpathApplication();
        $account = $this->createAccount(['email'=>'test@domain.com', 'password'=>'superP4ss!']);

        $result = $this->stub->authenticate('test@domain.com', 'superP4ss!');
        $accessToken = $result->getRefreshTokenString();

        $this->stub->queueRefreshToken($accessToken);

        $cookieJar = app('cookie');

        $this->assertTrue($cookieJar->hasQueued(config('stormpath.web.refreshTokenCookie.name')));
        $queuedCookie = $cookieJar->getQueuedCookies()['refresh_token'];

        $this->assertEquals(time()+5184000, $queuedCookie->getExpiresTime());

        $cookieJar->unqueue('access_token');
    }


}



class CookiesTraitStub
{
    use Cookies, AuthenticatesUser;
}
