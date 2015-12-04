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
        config(['stormpath.web.register.autoAuthorize.enabled' => true]);
        $this->setupStormpathApplication();
        $account = $this->createAccount(['username'=>'testUsername', 'email' => 'test@account.com', 'password' => 'superP4ss!']);
        $this->post('login', ['login' => 'test@account.com', 'password' => 'superP4ss!']);

        $this->get('testAuthenticateMiddleware');
        $this->see('Hello!');
    }


    /** @test */
    public function an_invalid_access_token_redirects_to_login_screen()
    {
        $this->setupStormpathApplication();
        session([config('stormpath.web.accessTokenCookie.name') => '123']);

        $this->get('testAuthenticateMiddleware');
        $this->assertRedirectedToRoute('stormpath.login');
    }

}
