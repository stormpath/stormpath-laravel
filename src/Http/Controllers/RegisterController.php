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

namespace Stormpath\Laravel\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Factory as Validator;
use Stormpath\Laravel\Http\Traits\AuthenticatesUser;
use Event;
use Stormpath\Laravel\Exceptions\ActionAbortedException;
use Stormpath\Laravel\Events\UserIsRegistering;
use Stormpath\Laravel\Events\UserHasRegistered;
use Stormpath\Laravel\Http\Traits\Cookies;
use Stormpath\Resource\Account;

class RegisterController extends Controller
{
    use AuthenticatesUser, Cookies;
    /**
     * @var Request
     */
    private $request;
    /**
     * @var Validator
     */
    private $validator;


    /**
     * LoginController constructor.
     * @param Request $request
     * @param Validator $validator
     */
    public function __construct(Request $request, Validator $validator)
    {
        $this->middleware('stormpath.produces');
        $this->request = $request;
        $this->validator = $validator;
    }

    public function getRegister()
    {
        if( config('stormpath.web.idSite.enabled') ) {
            return redirect(app('stormpath.application')->createIdSiteUrl(['path'=>config('stormpath.web.idSite.registerUri'), 'callbackUri'=>route('stormpath.idSiteResponse')]));
        }

        if($this->request->wantsJson()) {
            return $this->respondWithForm();
        }

        $status = $this->request->get('status');

        return view( config('stormpath.web.register.view'), compact('status') );
    }

    public function postRegister()
    {
        $validator = $this->registerValidator();

        if($validator->fails()) {

            if($this->request->wantsJson()) {
                return $this->respondWithValidationErrorForJson($validator);
            }

            return redirect()
                ->to(config('stormpath.web.register.uri'))
                ->withErrors($validator)
                ->withInput();
        }

        if(($errorFields = $this->isAcceptedPostFields($this->request->all())) !== true) {
            return $this->respondWithErrorJson('We do not allow arbitrary data to be posted to an account\'s custom data object. `'. array_shift($errorFields) . '` is either disabled or not defined in the config.', 400);
        }


        try {
            $registerFields = $this->setRegisterFields();

            // the form has passed validation. It's time to fire the
            // `UserIsRegistering` event
            //
            if (false===Event::fire(new UserIsRegistering($registerFields), [], true)) {
                throw new ActionAbortedException;
            }

            $account = \Stormpath\Resource\Account::instantiate($registerFields);

            app('cache.store')->forget('stormpath.application');
            $application = app('stormpath.application');

            $account = $application->createAccount($account);

            // the account has been created. Now I need to add any non-standard
            // fields from the `$registerFields` array to the
            // `$account->customData` object and re-save the account

            // a flag to track whether custom data has been added - if we don't
            // add any custom data, we don't need to re-save the account
            //
            $customDataAdded = false;

            // what follows here is a bit of a kludge. There is no easy way to
            // determine which values in the `$registerFields` array are
            // "normal" data and which are custom data for an account. This is
            // because the `instantiate` method simply sends all the data to the
            // server & doesn't check to see which values are used and which are
            // not. So in the loop below, I am checking each item in the
            // `$registerFields` array - if it exists as a property on the
            // `$account` object, then it doesn't need to be added as a custom
            // data value.
            //
            foreach ($registerFields as $key=>$value) {
                // make sure we're not adding the password or passwordConfirm
                // fields
                //
                if ($key!='password' && $key!='confirmPassword') {
                    if ($account->{$key}!=$registerFields[$key]) {
                        $account->customData->{$key} = $value;
                        $customDataAdded = true;
                    }
                }
            }

            // was any custom data added? if so, save the account object
            //
            if ($customDataAdded) {
                $account->save();
            }

            // the account has been created. Time to fire the
            // `UserHasRegistered` event.
            //
            Event::fire(new UserHasRegistered($account));
            if($this->request->wantsJson()) {
                return $this->respondWithAccount($account);
            }

            if(config('stormpath.web.verifyEmail.enabled') == true) {
                return redirect()
                    ->route('stormpath.login', ['status'=>'unverified']);
            }

            if(config('stormpath.web.register.autoAuthorize') == false) {
                return redirect()
                    ->route('stormpath.login', ['status'=>'created']);
            }

            $login = isset($registerFields['username']) ? $registerFields['username'] : null;
            $login = isset($registerFields['email']) ? $registerFields['email'] : $login;

            $result = $this->authenticate($login, $registerFields['password']);
            $this->queueAccessToken($result->getAccessTokenString());
            $this->queueRefreshToken($result->getRefreshTokenString());


            return redirect()
                ->to(config('stormpath.web.register.nextUri'));


        } catch(\Stormpath\Resource\ResourceError $re) {
            if($this->request->wantsJson()) {
                return $this->respondWithErrorJson($re->getMessage(), $re->getStatus());
            }
            return redirect()
                ->to(config('stormpath.web.register.uri'))
                ->withErrors(['errors'=>[$re->getMessage()]])
                ->withInput();
        }

    }

    private function registerValidator()
    {
        $rules = [];
        $messages = [];
        $input = $this->request->all();

        $registerField = config('stormpath.web.register.form.fields');

        foreach($registerField as $key => $field) {
            if($field['enabled'] == true && $field['required'] == true) {
                $rules[$key] = 'required';
            }
        }

        $messages['username.required'] = 'Username is required.';
        $messages['givenName.required'] = 'Given name is required.';
        $messages['middleName.required'] = 'Middle name is required.';
        $messages['surname.required'] = 'Surname is required.';
        $messages['email.required'] = 'Email is required.';
        $messages['password.required'] = 'Password is required.';
        $messages['confirmPassword.required'] = 'Password confirmation is required.';


        if( config('stormpath.web.register.form.fields.confirmPassword.enabled') ) {
            $rules['password'] = 'required|same:confirmPassword';
            $messages['password.same'] = 'Passwords are not the same.';
        }

        $validator = $this->validator->make(
            $this->request->all(),
            $rules,
            $messages
        );


        return $validator;
    }

    private function setRegisterFields()
    {
        $registerArray = [];
        $registerFields = config('stormpath.web.register.form.fields');
        foreach($registerFields as $spfield=>$field) {
            if($field['required'] == true) {
                $registerArray[$spfield] = $this->request->input($spfield);
            }
        }

        return $registerArray;
    }

    private function respondWithForm()
    {
        $fields = [];
        $fields[] = [
            'label' => 'csrf',
            'name' => '_token',
            'placeholder' => '',
            'value' => csrf_token(),
            'required' => true,
            'type' => 'hidden'
        ];
        foreach(config('stormpath.web.register.form.fields') as $field) {
            if($field['enabled'] == true) {
                $fields[] = $field;
            }
        }

        $data = [
            'form' => [
                'fields' => $fields
            ],
            'accountStores' => [
                app('cache.store')->get('stormpath.accountStores')
            ]
        ];

        return response()->json($data);
    }


    private function respondWithErrorJson($message, $statusCode = 400)
    {
        $error = [
            'message' => $message,
            'status' => $statusCode
        ];

        return response()->json($error, $statusCode);
    }


    private function respondWithAccount(Account $account)
    {
        $properties = ['account'=>[]];
        $config = config('stormpath.web.me.expand');
        $whiteListResources = [];
        foreach($config as $item=>$value) {
            if($value == true) {
                $whiteListResources[] = $item;
            }
        }

        $propNames = $account->getPropertyNames();
        foreach($propNames as $prop) {
            $property = $this->getPropertyValue($account, $prop);

            if(is_object($property) && !in_array($prop, $whiteListResources)) {
                continue;
            }

            $properties['account'][$prop] = $property;
        }
        return response()->json($properties);
    }

    private function getPropertyValue($account, $prop)
    {
        $value = null;
        try {
            $value = $account->getProperty($prop);
        } catch (\Exception $e) {}

        return $value;

    }

    private function respondWithValidationErrorForJson($validator)
    {

        return response()->json([
            'message' => $validator->errors()->first(),
            'status' => 400
        ], 400);
    }

    private function isAcceptedPostFields($submittedFields)
    {
        $fields = [];
        $allowedFields = config('stormpath.web.register.form.fields');

        foreach($allowedFields as $key => $value) {
            //Enabled check when iOS SDK is updated to not use username in tests
//            if($value['enabled'] == false) continue;
            $fields[] = $key;
        }
        $fields[] = '_token';

        if(!empty($diff = array_diff(array_keys($submittedFields), array_values($fields)))) {
            return $diff;
        }

        return true;
    }
}
