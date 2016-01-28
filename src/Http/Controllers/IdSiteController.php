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
use Stormpath\Laravel\Exceptions\IdSiteException;
use Stormpath\Laravel\Http\Traits\AuthenticatesUser;

class IdSiteController extends Controller
{

    public function response(Request $request)
    {

        try {
            $response = app('stormpath.application')->handleIdSiteCallback($request->fullUrl());

            switch ($response->status) {
                case 'AUTHENTICATED' :
                    return $this->authenticate($request);
                    break;
                case 'LOGOUT' :
                    return $this->logout();
                    break;
                case 'REGISTERED' :
                    return redirect(config('stormpath.web.login.uri'));
                    break;

            }
        } catch(\Stormpath\Resource\ResourceError $re) {
            throw new IdSiteException('ID Site Exception: ' . $re->getMessage());
        }
    }

    private function exchangeIdSiteToken($jwtResponse)
    {
        $exchangeIdSiteTokenRequest = new \Stormpath\Oauth\ExchangeIdSiteTokenRequest($jwtResponse);
        $auth = new \Stormpath\Oauth\ExchangeIdSiteTokenAuthenticator(app('stormpath.application'));
        return $auth->authenticate($exchangeIdSiteTokenRequest);
    }

    private function authenticate($request)
    {
        try {
            $result = $this->exchangeIdSiteToken($request->query('jwtResponse'));

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

        } catch(\Stormpath\Resource\ResourceError $re) {
            throw new IdSiteException('ID Site Exception: ' . $re->getMessage());
        }
    }

    private function logout()
    {
        return redirect()
            ->to(config('stormpath.web.logout.nextUri'))
            ->withCookies([
                cookie()->forget(config('stormpath.web.accessTokenCookie.name')),
                cookie()->forget(config('stormpath.web.refreshTokenCookie.name'))
            ]);
    }
}
