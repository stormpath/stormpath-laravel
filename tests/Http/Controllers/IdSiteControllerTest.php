<?php

namespace Stormpath\Laravel\Tests\Http\Controllers;

use Stormpath\Laravel\Tests\TestCase;

class IdSiteControllerTest extends TestCase
{
    public function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        config(['stormpath.web.idSite.enabled' => true]);
        config(['stormpath.web.login.enabled' => true]);
        config(['stormpath.web.logout.enabled' => true]);
        config(['stormpath.web.register.enabled' => true]);
        config(['stormpath.web.forgotPassword.enabled' => true]);
    }

    /** @test */
    public function login_will_redirect_to_id_site()
    {
        $this->setupStormpathApplication();
        $this->get('login');
        $this->assertRedirectedContains('https://api.stormpath.com');

    }

    /** @test */
    public function logout_will_redirect_to_id_site()
    {
        $this->setupStormpathApplication();
        $this->post('logout');
        $this->assertRedirectedContains('https://api.stormpath.com');

    }

    /** @test */
    public function register_will_redirect_to_id_site()
    {
        $this->setupStormpathApplication();
        $this->get('register');
        $this->assertRedirectedContains('https://api.stormpath.com');

    }

    /** @test */
    public function forgot_will_redirect_to_id_site()
    {
        $this->setupStormpathApplication();
        $this->get('forgot');
        $this->assertRedirectedContains('https://api.stormpath.com');

    }






    private function assertRedirectedContains($uri, $with = [])
    {
        $this->assertInstanceOf('Illuminate\Http\RedirectResponse', $this->response);

        $this->assertContains($this->app['url']->to($uri), $this->response->headers->get('Location'));

        $this->assertSessionHasAll($with);
    }


}