<?php

namespace Stormpath\Laravel\Tests\Http\Middleware;

use Stormpath\Laravel\Tests\TestCase;

class RedirectIfAuthenticatedTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->app->router->get(
            '/',
            function() {
                return 'Home';
            }
        );

        $this->app->router->get(
            'testRedirectIfAuthenticatedMiddleware',
            [
                'middleware'=>'stormpath.guest',
                function() {
                    return 'Hello!';
                }
            ]
        );
    }

    /** @test */
    public function it_coninutes_if_user_is_a_guest()
    {
        $this->get('testRedirectIfAuthenticatedMiddleware');
        $this->see('Hello!');
    }

    /** @test */
    public function it_redirects_home_if_user_is_authenticated()
    {
        session([config('stormpath.web.accessTokenCookie.name') => '123']);
        session([config('stormpath.web.refreshTokenCookie.name') => '123']);

        $this->get('testRedirectIfAuthenticatedMiddleware');
        $this->assertRedirectedTo('/');
        $this->followRedirects();
        $this->see('Home');
    }




}
