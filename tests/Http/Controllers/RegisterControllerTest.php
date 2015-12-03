<?php

namespace Stormpath\Tests\Http\Controllers;

use Stormpath\Laravel\Tests\TestCase;

class RegisterControllerTest extends TestCase
{
//    /** @test */
//    public function it_shows_register_page_if_enabled()
//    {
//        config(['stormpath.web.register.enabled'=>true]);
//        $this->visit('register')->assertResponseOk();
//    }

    /** @test */
    public function it_requires_a_username_if_set_to_required()
    {
        $this->registerWithout('username');
        $this->assertSessionHasErrors([config('stormpath.web.register.fields.username.name')=>'Username is required.']);
    }

    /** @test */
    public function it_requires_a_given_name_if_set_to_required()
    {
        $this->registerWithout('givenName');
        $this->assertSessionHasErrors([config('stormpath.web.register.fields.givenName.name')=>'Given name is required.']);
    }

    /** @test */
    public function it_requires_a_middle_name_if_set_to_required()
    {
        $this->registerWithout('middleName');
        $this->assertSessionHasErrors([config('stormpath.web.register.fields.middleName.name')=>'Middle name is required.']);
    }

    /** @test */
    public function it_requires_a_surname_if_set_to_required()
    {
        $this->registerWithout('surname');
        $this->assertSessionHasErrors([config('stormpath.web.register.fields.surname.name')=>'Surname is required.']);
    }

    /** @test */
    public function it_requires_a_email_if_set_to_required()
    {
        $this->registerWithout('email');
        $this->assertSessionHasErrors([config('stormpath.web.register.fields.email.name')=>'Email is required.']);
    }

    /** @test */
    public function it_requires_a_password_if_set_to_required()
    {
        $this->registerWithout('password');
        $this->assertSessionHasErrors([config('stormpath.web.register.fields.password.name')=>'Password is required.']);
    }

    /** @test */
    public function it_requires_a_password_confirm_if_set_to_required()
    {
        $this->registerWithout('passwordConfirm');
        $this->assertSessionHasErrors([config('stormpath.web.register.fields.passwordConfirm.name')=>'Password confirmation is required.']);
    }

    /** @test */
    public function it_requires_password_to_be_confirmed_if_password_confirm_set_to_required()
    {
        config(["stormpath.web.register.fields.passwordConfirm.required"=>true]);
        $this->post('register', [
            'username' => 'testUsername',
            'givenName'=>'Test',
            'middleName' => 'Middle',
            'surname' => 'Account',
            'email' => 'test@account.com',
            'password' => 'superP4ss!',
            'password_confirmation' => 'superP4ss'
        ]);
        $this->assertRedirectedTo(config('stormpath.web.register.uri'));
        $this->followRedirects();
        $this->seePageIs(config('stormpath.web.register.uri'));
        $this->see('Create Account');

        $this->assertSessionHasErrors(['password'=>'Passwords are not the same.']);
        $this->assertHasOldInput();
    }

    /** @test */
    public function it_auto_authenticates_during_successful_registration_if_enabled()
    {
        $this->setupStormpathApplication();
        config(["stormpath.web.register.autoAuthorize"=>true]);

        $this->post('register', [
            config('stormpath.web.register.fields.username.name') => 'testUsername',
            config('stormpath.web.register.fields.givenName.name')=>'Test',
            config('stormpath.web.register.fields.middleName.name') => 'Middle',
            config('stormpath.web.register.fields.surname.name') => 'Account',
            config('stormpath.web.register.fields.email.name') => 'test@account.com',
            config('stormpath.web.register.fields.password.name') => 'superP4ss!',
            config('stormpath.web.register.fields.passwordConfirm.name') => 'superP4ss!'
        ]);

        $this->assertSessionHas(config('stormpath.web.accessTokenCookie.name'));
        $this->assertSessionHas(config('stormpath.web.refreshTokenCookie.name'));

        $this->assertRedirectedTo(config('stormpath.web.register.nextUri'));
    }

    /** @test */
    public function it_does_not_authenticate_during_successful_registration_if_disabled()
    {
        $this->setupStormpathApplication();
        config(["stormpath.web.register.autoAuthorize"=>false]);

        $this->post('register', [
            config('stormpath.web.register.fields.username.name') => 'testUsername',
            config('stormpath.web.register.fields.givenName.name')=>'Test',
            config('stormpath.web.register.fields.middleName.name') => 'Middle',
            config('stormpath.web.register.fields.surname.name') => 'Account',
            config('stormpath.web.register.fields.email.name') => 'test@account.com',
            config('stormpath.web.register.fields.password.name') => 'superP4ss!',
            config('stormpath.web.register.fields.passwordConfirm.name') => 'superP4ss!'
        ]);

        $this->assertNull($this->app['session']->get(config('stormpath.web.accessTokenCookie.name')));
        $this->assertNull($this->app['session']->get(config('stormpath.web.refreshTokenCookie.name')));

        $this->assertRedirectedTo(config('stormpath.web.register.nextUri'));
    }

    /** @test */
    public function it_returns_to_registration_if_login_is_already_taken()
    {
        $this->setupStormpathApplication();
        config(["stormpath.web.register.autoAuthorize"=>false]);

        $account = $this->createAccount(['username'=>'testUsername', 'email' => 'test@account.com', 'password' => 'superP4ss!']);

        $this->post('register', [
            config('stormpath.web.register.fields.username.name') => 'testUsername',
            config('stormpath.web.register.fields.givenName.name')=>'Test',
            config('stormpath.web.register.fields.middleName.name') => 'Middle',
            config('stormpath.web.register.fields.surname.name') => 'Account',
            config('stormpath.web.register.fields.email.name') => 'test@account.com',
            config('stormpath.web.register.fields.password.name') => 'superP4ss!',
            config('stormpath.web.register.fields.passwordConfirm.name') => 'superP4ss!'
        ]);

        $this->assertRedirectedTo(config('stormpath.web.register.uri'));
        $this->followRedirects();
        $this->seePageIs('register');
        $this->see('Create Account');

        $this->assertContains('Account with that email already exists.  Please choose another email.',$this->app['session']->get('errors')->all());
        $this->assertHasOldInput();
        $account->delete();
    }


    private function registerWithout($field)
    {
        $without = [];
        $fieldName = config("stormpath.web.register.fields.{$field}.name");
        $without[$fieldName] = null;

        config(["stormpath.web.register.fields.{$field}.required"=>true]);
        $this->post('register', array_merge([
            config('stormpath.web.register.fields.username.name') => 'testUsername',
            config('stormpath.web.register.fields.givenName.name')=>'Test',
            config('stormpath.web.register.fields.middleName.name') => 'Middle',
            config('stormpath.web.register.fields.surname.name') => 'Account',
            config('stormpath.web.register.fields.email.name') => 'test@account.com',
            config('stormpath.web.register.fields.password.name') => 'superP4ss!',
            config('stormpath.web.register.fields.passwordConfirm.name') => 'superP4ss!'
        ], $without));

        $this->assertRedirectedTo(config('stormpath.web.register.uri'));
        $this->followRedirects();
        $this->seePageIs(config('stormpath.web.register.uri'));
        $this->see('Create Account');

        $this->assertHasOldInput();

        return $this;
    }


}
