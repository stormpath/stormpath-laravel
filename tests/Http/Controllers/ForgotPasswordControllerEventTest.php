<?php
/*
 * Copyright 2015 Stormpath, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Stormpath\Tests\Http\Controllers;

use Stormpath\Laravel\Tests\TestCase;

class ForgotPasswordControllerEventTest extends TestCase
{
    public function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        config(['stormpath.web.forgotPassword.enabled'=>true]);
    }

/*
it will trigger the UserHasRequestedPasswordReset event when the password request is submitted
*/


    /** @test */
    public function a_valid_email_will_redirect_to_login_screen_with_status_forgot_and_display_message()
    {
        $this->expectsEvents(\Stormpath\Laravel\Events\UserHasRequestedPasswordReset::class);

        $this->setupStormpathApplication();
        $this->createAccount(['email'=>'test@test.com']);
        $this->post(route('stormpath.forgotPassword'), ['email'=>'test@test.com']);

        $this->assertRedirectedTo(config('stormpath.web.forgotPassword.nextUri'));
        $this->followRedirects();
        $this->seePageIs(config('stormpath.web.forgotPassword.nextUri'));
        $this->see('Password Reset Requested');

    }
}
