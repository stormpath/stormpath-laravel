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
use Stormpath\Stormpath;

class RegisterControllerCustomFieldsTest extends TestCase
{

    public function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        config(['stormpath.web.register.enabled'=>true]);

    }

    /** @test */
    public function it_saves_custom_fields_in_customData_upon_successful_registration()
    {
        $this->setupStormpathApplication();

        $this->post('register', [
            config('stormpath.web.register.form.fields.username.name') => 'testUsername',
            config('stormpath.web.register.form.fields.givenName.name')=>'Test',
            config('stormpath.web.register.form.fields.middleName.name') => 'Middle',
            config('stormpath.web.register.form.fields.surname.name') => 'Account',
            config('stormpath.web.register.form.fields.email.name') => 'test@account.com',
            config('stormpath.web.register.form.fields.password.name') => 'superP4ss!',
            config('stormpath.web.register.form.fields.passwordConfirm.name') => 'superP4ss!',
            'customData1' => 'some',
            'customData2' => 'custom',
            'customData3' => 'data',
        ]);

        // get the application object
        $application = app('stormpath.application');

        // find the account we just created
        $accounts = $application->accounts;
        $accounts->search = [config('stormpath.web.register.form.fields.email.name') => 'test@account.com'];

        // make sure we got exactly 1 account in this search
        $this->assertEquals(1, $accounts->getSize());

        // get the account
        $account = $accounts->getIterator()->current();

        // test the custom data values
        $this->assertEquals('some', $account->customData->customData1);
        $this->assertEquals('custom', $account->customData->customData2);
        $this->assertEquals('data', $account->customData->customData3);
    }

}
