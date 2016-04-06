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

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Factory as Validator;
use Stormpath\Laravel\Http\Traits\AuthenticatesUser;
use Stormpath\Laravel\Http\Traits\Cookies;
use Stormpath\Resource\AccessToken;
use Stormpath\Resource\Account;
use Stormpath\Resource\RefreshToken;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Event;
use Stormpath\Laravel\Exceptions\ActionAbortedException;
use Stormpath\Laravel\Events\UserIsLoggingIn;
use Stormpath\Laravel\Events\UserHasLoggedIn;
use Stormpath\Laravel\Events\UserIsLoggingOut;

class LoginController extends Controller
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

    public function getLogin()
    {
        if( config('stormpath.web.idSite.enabled') ) {
            return redirect(app('stormpath.application')->createIdSiteUrl(['callbackUri'=>route('stormpath.idSiteResponse')]));
        }

        if($this->request->wantsJson()) {
            return $this->respondWithForm();
        }

        $status = $this->request->get('status');

        return view(config('stormpath.web.login.view'), compact('status'));

    }

    public function postLogin()
    {
        if($this->isSocialLoginAttempt()) {
            return $this->doSocialLogin();
        }


        $validator = $this->loginValidator();



        if($validator->fails()) {
            if($this->request->wantsJson()) {
                return $this->respondWithValidationErrorForJson($validator);
            }

            return redirect()
                ->to(config('stormpath.web.login.uri'))
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // the login request data has passed validation. Time to fire the
            // UserIsLoggingIn event
            //
            if (false===Event::fire(new UserIsLoggingIn(['login'=> $this->request->input('login'), 'password'=> $this->request->input('password')]), [], true)) {
                throw new ActionAbortedException;
            }

            $result = $this->authenticate($this->request->input('login'), $this->request->input('password'));
            $this->queueAccessToken($result->getAccessTokenString());
            $this->queueRefreshToken($result->getRefreshTokenString());

            $account = $result->getAccessToken()->getAccount();

            Event::fire(new UserHasLoggedIn($account));

            if($this->request->wantsJson()) {
                return $this->respondWithAccount($account);
            }



            return redirect()
                ->intended(config('stormpath.web.login.nextUri'));

        } catch (\Stormpath\Resource\ResourceError $re) {

            if($this->request->wantsJson()) {
                return $this->respondWithError($re->getMessage(), $re->getStatus());
            }

            return redirect()
                ->to(config('stormpath.web.login.uri'))
                ->withErrors(['errors'=>[$re->getMessage()]])
                ->withInput();
        }
    }

    public function getLogout()
    {

        if( config('stormpath.web.idSite.enabled') ) {
            return redirect(app('stormpath.application')->createIdSiteUrl(['logout'=>true, 'callbackUri'=>route('stormpath.idSiteResponse')]));
        }

        if (false===Event::fire(new UserIsLoggingOut, [], true)) {
            throw new ActionAbortedException;
        }

        if($this->request->wantsJson()) {
            return response()
                ->json()
                ->withCookie(
                    cookie()->forget(config('stormpath.web.accessTokenCookie.name'))
                )
                ->withCookie(
                    cookie()->forget(config('stormpath.web.refreshTokenCookie.name'))
                );

//            $this->removeTokens($this->request);

//            return response();
        }

        return redirect()
            ->to(config('stormpath.web.logout.nextUri'))
            ->withCookies([
                cookie()->forget(config('stormpath.web.accessTokenCookie.name')),
                cookie()->forget(config('stormpath.web.refreshTokenCookie.name'))
            ]);
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

    private function respondWithForm()
    {

        $data = [
            'form' => [
                'fields' => [
                    [
                        'label' => 'Username or Email',
                        'name' => 'login',
                        'placeholder' => 'Username or Email',
                        'required' => true,
                        'type' => 'text'
                    ],
                    [
                        'label' => 'Password',
                        'name' => 'password',
                        'placeholder' => 'Password',
                        'required' => true,
                        'type' => 'password'
                    ],
                    [
                        'label' => 'csrf',
                        'name' => '_token',
                        'placeholder' => '',
                        'value' => csrf_token(),
                        'required' => true,
                        'type' => 'hidden'
                    ]
                ]
            ],
            'accountStores' => [
                app('cache.store')->get('stormpath.accountStores')
            ],

        ];
        return response()->json($data);

    }

    private function respondWithError($message, $statusCode = 400)
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

    private function isSocialLoginAttempt()
    {
        $attempt = $this->request->has('providerData');

        if(!$attempt) {
            return false;
        }

        switch ($provider = $this->request->input('providerData')['providerId'])
        {
            /** @codeCoverageIgnoreStart */
            case 'google' :
            case 'facebook' :
            case 'linkedin' :
                return true;
            /** @codeCoverageIgnoreEnd */
            case 'stormpath' :
                throw new \InvalidArgumentException("Please use the standard login/password method instead");
            default :
                throw new \InvalidArgumentException("The social provider {$provider} is not supported");
        }
    }

    /** @codeCoverageIgnore */
    private function doSocialLogin()
    {
        switch ($provider = $this->request->input('providerData')['providerId'])
        {
            case 'google' :
                return app(SocialCallbackController::class)->google($this->request);
            case 'facebook' :
                return app(SocialCallbackController::class)->facebook($this->request);
            case 'linkedin' :
                return app(SocialCallbackController::class)->linkedin($this->request);

        }
    }

    private function respondWithValidationErrorForJson($validator)
    {

        return response()->json([
            'message' => $validator->errors()->first(),
            'status' => 400
        ], 400);
    }

}
