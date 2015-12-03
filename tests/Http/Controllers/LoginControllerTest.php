<?php

namespace Stormpath\Tests\Http\Controllers;

use Mockery as m;
use Stormpath\Laravel\Tests\TestCase;

class LoginControllerTest extends TestCase
{
    /** @test */
    public function it_requires_a_login_to_be_submitted()
    {
        $this->post('login', ['password'=>'superPassword']);
        $this->assertRedirectedTo(config('stormpath.web.login.uri'));
        $this->followRedirects();
        $this->seePageIs('login');
        $this->see('Log In');

        $this->assertSessionHasErrors(['login'=>'Login is required.']);
        $this->assertHasOldInput();
    }

    /** @test */
    public function it_requires_a_password_to_be_submitted()
    {
        $this->post('login', ['login' => 'someLogin']);
        $this->assertRedirectedTo(config('stormpath.web.login.uri'));
        $this->followRedirects();
        $this->seePageIs('login');
        $this->see('Log In');

        $this->assertSessionHasErrors(['password' => 'Password is required.']);
        $this->assertHasOldInput();

    }

    /** @test */
    public function successful_login_sets_session_variables()
    {
        $this->setupStormpathApplication();
        $account = $this->createAccount(['login' => 'test@test.com', 'password' => 'superP4ss!']);
        $this->post('login', ['login' => 'test@test.com', 'password' => 'superP4ss!']);
        $this->assertSessionHas(config('stormpath.web.accessTokenCookie.name'));
        $this->assertSessionHas(config('stormpath.web.refreshTokenCookie.name'));
        $account->delete();
    }

    /** @test */
    public function will_display_error_if_account_is_invalid()
    {
        $this->setupStormpathApplication();
        $this->post('login', ['login' => 'somelogin', 'password' => 'somePassword']);
        $this->assertRedirectedTo(config('stormpath.web.login.uri'));
        $this->followRedirects();
        $this->seePageIs('login');
        $this->see('Log In');

        $this->assertSessionHasErrors(['errors'=>'Invalid username or password.']);
        $this->assertHasOldInput();
    }

    /** @test */
    public function will_redirect_to_next_uri_on_login()
    {
        $this->setupStormpathApplication();
        $account = $this->createAccount(['login' => 'test@test.com', 'password' => 'superP4ss!']);
        $this->post('login', ['login' => 'test@test.com', 'password' => 'superP4ss!']);
        $this->assertRedirectedTo(config('stormpath.web.login.nextUri'));
        $account->delete();

    }

}
