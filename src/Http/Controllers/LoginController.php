<?php

namespace Stormpath\Laravel\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Factory as Validator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class LoginController extends Controller
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

    public function getLogin()
    {
        $status = $this->request->get('status');

        return view( config('stormpath.web.login.view'), compact('status') );
    }

    public function postLogin()
    {

        $validator = $this->loginValidator();

        if($validator->fails()) {
            return redirect()
                ->to(config('stormpath.web.login.uri'))
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $passwordGrant = new \Stormpath\Oauth\PasswordGrantRequest($this->request->get('login'), $this->request->get('password'));
            $auth = new \Stormpath\Oauth\PasswordGrantAuthenticator(app('stormpath.application'));
            $result = $auth->authenticate($passwordGrant);

            session([config('stormpath.web.accessTokenCookie.name') => $result->getAccessTokenString()]);
            session([config('stormpath.web.refreshTokenCookie.name') => $result->getRefreshTokenString()]);

            return redirect()
                ->to(config('stormpath.web.login.nextUri'));
        } catch (\Stormpath\Resource\ResourceError $re) {
            return redirect()
                ->to(config('stormpath.web.login.uri'))
                ->withErrors(['errors'=>[$re->getMessage()]])
                ->withInput();
        }
    }

    private function loginValidator()
    {
        $validator = $this->validator->make(
            $this->request->all(),
            [
                'login' => 'required',
                'password' => 'required'
            ],
            [
                'login.required' => 'Login is required.',
                'password.required' => 'Password is required.'
            ]
        );


        return $validator;
    }
}