<?php

namespace Stormpath\Laravel\Tests\Http\Middleware;

use Stormpath\Laravel\Tests\TestCase;

class AuthenticateTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->app->router->get(
            'testAuthenticateMiddleware',
            [
                'middleware'=>'stormpath.auth',
                function() {
                    return 'Hello!';
                }
            ]
        );

    }

    /** @test */
    public function it_redirects_if_user_is_a_guest()
    {
        $this->get('testAuthenticateMiddleware');
        $this->assertRedirectedToRoute('stormpath.login');
    }

    /** @test */
    public function it_continues_if_user_is_authenticated()
    {
        session([config('stormpath.web.accessTokenCookie.name') => '123']);
        session([config('stormpath.web.refreshTokenCookie.name') => '123']);

        $this->get('testAuthenticateMiddleware');
        $this->see('Hello!');
    }

}
