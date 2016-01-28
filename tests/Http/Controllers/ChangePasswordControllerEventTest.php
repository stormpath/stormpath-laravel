<?php

namespace Stormpath\Laravel\Tests\Http\Controllers;

use Stormpath\Laravel\Tests\TestCase;

class ChangePasswordControllerEventTest extends TestCase
{
    public function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        config(['stormpath.web.changePassword.enabled'=>true]);
        config(['stormpath.web.forgotPassword.enabled'=>true]);
    }

    /** @test */
    public function it_will_trigger_the_UserHasResetPassword_event_when_the_password_is_updated()
    {
        $this->expectsEvents(\Stormpath\Laravel\Events\UserHasResetPassword::class);

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
