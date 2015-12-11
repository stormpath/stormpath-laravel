<?php

namespace Stormpath\Laravel\Tests\Http\Controllers;

use Stormpath\Laravel\Tests\TestCase;

class ResetPasswordControllerTest extends TestCase
{
    public function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        config(['stormpath.web.changePassword.enabled'=>true]);
        config(['stormpath.web.forgotPassword.enabled'=>true]);
    }

    /** @test */
    public function it_should_validate_the_existance_of_sp_token_and_redirect_to_error_uri_if_it_does_not_exist()
    {
        $this->get(route('stormpath.changePassword'))
            ->assertRedirectedTo(config('stormpath.web.changePassword.errorUri'));

        $this->followRedirects()
            ->seePageIs(config('stormpath.web.changePassword.errorUri'))
            ->see('The password reset link you tried to use is no longer valid.');
    }

    /** @test */
    public function if_sp_token_is_invalid_it_should_redirect_to_error_uri()
    {
        $this->setupStormpathApplication();
        $this->get(route('stormpath.changePassword').'?spToken=123')
            ->assertRedirectedTo(config('stormpath.web.changePassword.errorUri'));

        $this->followRedirects()
            ->seePageIs(config('stormpath.web.changePassword.errorUri'))
            ->see('The password reset link you tried to use is no longer valid.');
    }

    /** @test */
    public function if_sp_token_is_valid_it_should_show_form()
    {
        $this->setupStormpathApplication();
        $account = $this->createAccount();
        $token = $this->createValidToken($account);

        $this->get(route('stormpath.changePassword').'?spToken='.$token)
            ->see('Change Your Password');

    }

    /** @test */
    public function a_valid_sp_token_allows_user_to_change_password()
    {
        $this->setupStormpathApplication();
        $account = $this->createAccount();
        $token = $this->createValidToken($account);

        $this->visit(route('stormpath.changePassword').'?spToken='.$token)
            ->submitForm('Submit', [
                'password'=>'s0methingEls3!',
                'password_confirmation'=>'s0methingEls3!'
            ]);

        $this->followRedirects();
        $this->see('Log In');
        $this->seePageIs(config('stormpath.web.changePassword.nextUri'));
        $this->see('Password Reset Successfuly');
    }

    /** @test */
    public function using_different_passwords_does_not_change_password_and_redirects_back_to_change_password_page()
    {
        $this->setupStormpathApplication();
        $account = $this->createAccount();
        $token = $this->createValidToken($account);

        $this->post(route('stormpath.changePassword').'?spToken='.$token,
            [
                'password'=>'s0methingEls3!',
                'password_confirmation'=>'n0tTh3s4me!'
            ]);
        $this->assertSessionHasErrors(['password'=>'Passwords do not match.']);
        $this->assertRedirectedTo(route('stormpath.changePassword').'?spToken='.$token);
    }

    /** @test */
    public function an_invalid_token_that_is_posted_to_change_password_will_redirect_to_change_password_page_with_error()
    {
        $this->setupStormpathApplication();

        $this->post(route('stormpath.changePassword').'?spToken=123',
            [
                'password'=>'s0methingEls3!',
                'password_confirmation'=>'s0methingEls3!'
            ]);

        $this->assertSessionHasErrors(['errors'=>'This password reset request does not exist. Please request a new password reset.']);
    }

    /**
     * @param $email
     * @return mixed
     */
    private function createValidToken($email)
    {
        $token = $this->application->sendPasswordResetEmail($email->email, [], true);
        $parts = $token->href;
        $parts = explode('/', $parts);
        $token = end($parts);
        return $token;
    }
}
