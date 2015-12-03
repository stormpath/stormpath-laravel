<?php

namespace Stormpath\Laravel\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Factory as Validator;

class RegisterController extends Controller
{
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
        $this->request = $request;
        $this->validator = $validator;
    }

    public function getRegister()
    {
        $status = $this->request->get('status');

        return view( config('stormpath.web.register.view'), compact('status') );
    }

    public function postRegister()
    {
        $validator = $this->registerValidator();

        if($validator->fails()) {
            return redirect()
                ->to(config('stormpath.web.register.uri'))
                ->withErrors($validator)
                ->withInput();
        }
        try {
            $registerFields = $this->setRegisterFields();

            $account = \Stormpath\Resource\Account::instantiate($registerFields);

            $application = app('stormpath.application');

            $account = $application->createAccount($account);

            dd($account);
        } catch(\Stormpath\Resource\ResourceError $re) {
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

        $registerField = config('stormpath.web.register.fields');

        foreach($registerField as $field) {
            if($field['required'] == true) {
                $rules[$field['name']] = 'required';
            }
        }

        $messages[config('stormpath.web.register.fields.username.name').'.required'] = 'Username is required.';
        $messages[config('stormpath.web.register.fields.givenName.name').'.required'] = 'Given name is required.';
        $messages[config('stormpath.web.register.fields.middleName.name').'.required'] = 'Middle name is required.';
        $messages[config('stormpath.web.register.fields.surname.name').'.required'] = 'Surname is required.';
        $messages[config('stormpath.web.register.fields.email.name').'.required'] = 'Email is required.';
        $messages[config('stormpath.web.register.fields.password.name').'.required'] = 'Password is required.';
        $messages[config('stormpath.web.register.fields.passwordConfirm.name').'.required'] = 'Password confirmation is required.';


        if( config('stormpath.web.register.fields.passwordConfirm.required') ) {
            $rules['password'] = 'required|confirmed';
            $messages['password.confirmed'] = 'Passwords are not the same.';
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
        $registerFields = config('stormpath.web.register.fields');
        foreach($registerFields as $spfield=>$field) {
            if($field['required'] == true) {
                $registerArray[$spfield] = $this->request->get($field['name']);
            }
        }

        return $registerArray;
    }
}