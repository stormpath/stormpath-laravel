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

namespace Stormpath\Laravel\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Cookie\CookieJar;

class RedirectIfAuthenticated
{
    private $cookieJar;

    public function __construct(CookieJar $cookieJar)
    {
        $this->cookieJar = $cookieJar;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($this->isAuthenticated($request)) {
            return redirect()->intended('/');
        }

        if($request->wantsJson()) {
            return response(null, 401);
        }

        $accessToken = $this->refreshCookie($request);

        if (null !== $accessToken) {
            return redirect()->intended('/');
        }

        return $next($request);
    }


    public function isAuthenticated($request)
    {
        $cookie = $request->cookie(config('stormpath.web.accessTokenCookie.name'));

        if(null === $cookie) {
            return false;
        }

        if($cookie instanceof \Symfony\Component\HttpFoundation\Cookie) {
            $cookie = $cookie->getValue();
        }

        try {
            (new \Stormpath\Oauth\VerifyAccessToken(app('stormpath.application')))->verify($cookie);
            return true;
        } catch (\Exception $re) {
            return false;
        }
    }

    private function refreshCookie($request)
    {
        try {
            $spApplication = app('stormpath.application');
        } catch (\Exception $e) {
            return null;
        }

        $cookie = $request->cookie(config('stormpath.web.refreshTokenCookie.name'));
        if($cookie instanceof \Symfony\Component\HttpFoundation\Cookie)
            $cookie = $cookie->getValue();

        try {
            $refreshGrant = new \Stormpath\Oauth\RefreshGrantRequest($cookie);
            $auth = new \Stormpath\Oauth\RefreshGrantAuthenticator($spApplication);
            $result = $auth->authenticate($refreshGrant);

            $this->setNewAccessToken($request, $result);

            return $result->getAccessTokenString();

        } catch(\Stormpath\Resource\ResourceError $re) {
            return null;
        }
    }

    private function setNewAccessToken($request, $cookies)
    {
        $this->cookieJar->queue(
            cookie(
                config('stormpath.web.accessTokenCookie.name'),
                $cookies->getAccessTokenString(),
                $cookies->getExpiresIn(),
                config('stormpath.web.accessTokenCookie.path'),
                config('stormpath.web.accessTokenCookie.domain'),
                config('stormpath.web.accessTokenCookie.secure'),
                config('stormpath.web.accessTokenCookie.httpOnly')
            )

        );


        $request->cookies->add([config('stormpath.web.accessTokenCookie.name') => $cookies->getAccessTokenString() ]);

    }
}
