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
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Factory as Validator;
use Stormpath\Laravel\Http\Traits\AuthenticatesUser;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Event;
use Stormpath\Laravel\Exceptions\ActionAbortedException;
use Stormpath\Laravel\Events\UserIsLoggingIn;
use Stormpath\Laravel\Events\UserHasLoggedIn;

class LoginController extends Controller
{

    use AuthenticatesUser;

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
            // the login request data has passed validation. Time to fire the
            // UserIsLoggingIn event
            //
            if (false===Event::fire(new UserIsLoggingIn(['login'=> $this->request->input('login'), 'password'=> $this->request->input('password')]), [], true)) {
                throw new ActionAbortedException;
            }

            $result = $this->authenticate($this->request->input('login'), $this->request->input('password'));

            $account = $result->getAccessToken()->getAccount();

            Event::fire(new UserHasLoggedIn($account));

            if($this->request->wantsJson()) {
                return $this->respondWithAccount($account);
            }

            return redirect()
                ->intended(config('stormpath.web.login.nextUri'))
                ->withCookies(
                    [
                        config('stormpath.web.accessTokenCookie.name') =>
                            cookie(
                                config('stormpath.web.accessTokenCookie.name'),
                                $result->getAccessTokenString(),
                                $result->getExpiresIn(),
                                config('stormpath.web.accessTokenCookie.path'),
                                config('stormpath.web.accessTokenCookie.domain'),
                                config('stormpath.web.accessTokenCookie.secure'),
                                config('stormpath.web.accessTokenCookie.httpOnly')
                            ),
                        config('stormpath.web.refreshTokenCookie.name') =>
                            cookie(
                                config('stormpath.web.refreshTokenCookie.name'),
                                $result->getRefreshTokenString(),
                                $result->getExpiresIn(),
                                config('stormpath.web.refreshTokenCookie.path'),
                                config('stormpath.web.refreshTokenCookie.domain'),
                                config('stormpath.web.refreshTokenCookie.secure'),
                                config('stormpath.web.refreshTokenCookie.httpOnly')
                            )
                    ]
                );

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
        $application = app('stormpath.application');
        $accountStoreArray = [];
        $accountStores = $application->getAccountStoreMappings();
        foreach($accountStores as $accountStore) {
            $store = $accountStore->accountStore;
            $provider = $store->provider;
            $accountStoreArray[] = [
                'href' => $store->href,
                'name' => $store->name,
                'provider' => [
                    'href' => $provider->href,
                    'providerId' => $provider->providerId,
                    'clientId' => $provider->clientId
                ]
            ];
        }

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
                $accountStoreArray
            ],

        ];

        return response()->json($data);

    }

    private function respondWithError($message, $statusCode = 400)
    {
        $error = [
            'errors' => [
                'message' => $message
            ]
        ];
        return response()->json($error, $statusCode);
    }

    private function respondWithAccount($account)
    {
        $properties = [];
        $blacklistProperties = [
            'providerData',
            'httpStatus',
            'createdAt',
            'modifiedAt'
        ];

        $propNames = $account->getPropertyNames();
        foreach($propNames as $prop) {
            if(in_array($prop, $blacklistProperties)) continue;
            $properties[$prop] = $this->getPropertyValue($account, $prop);
        }

        return response()->json($properties);
    }

    private function getPropertyValue($account, $propName)
    {
        if(is_object($account->{$propName})) {
            return ['href'=>$account->{$propName}->href];
        }

        return $account->{$propName};
    }
}
