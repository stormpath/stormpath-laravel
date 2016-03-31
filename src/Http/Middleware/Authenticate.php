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
use Illuminate\Cookie\CookieJar;
use Illuminate\Http\Request;
use Stormpath\Authc\Api\OAuthBearerRequestAuthenticator;

class Authenticate
{
    private $cookieJar;

    public function __construct(CookieJar $cookieJar)
    {
        $this->cookieJar = $cookieJar;
    }

    /**
     * Handle an incoming request to make sure a user is authenticated to allow them to view the route
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if ($this->isAuthenticated($request)) {
            return $next($request);
        }

        if ($this->refreshTokens($request)) {
            return $next($request);
        }

        return $this->responseUnauthenticated($request);

    }

    public function isAuthenticated(Request $request)
    {
        $token = $request->bearerToken();

        if(null === $token) {
            $token = $request->cookie(config('stormpath.web.accessTokenCookie.name'));
        }

        if($token instanceof \Symfony\Component\HttpFoundation\Cookie) {
            $token = $token->getValue();
        }

        try {
            (new \Stormpath\Oauth\VerifyAccessToken(app('stormpath.application')))->verify($token);
            return true;
        } catch (\Exception $re) {
            return false;
        }
    }

    public function refreshTokens(Request $request) {
        if ($request->wantsJson()) {
            return false;
        }

        try {
            $spApplication = app('stormpath.application');
        } catch (\Exception $e) {
            return false;
        }

        $cookie = $request->cookie(config('stormpath.web.refreshTokenCookie.name'));
        if($cookie instanceof \Symfony\Component\HttpFoundation\Cookie)
            $cookie = $cookie->getValue();

        try {
            $refreshGrant = new \Stormpath\Oauth\RefreshGrantRequest($cookie);
            $auth = new \Stormpath\Oauth\RefreshGrantAuthenticator($spApplication);
            $result = $auth->authenticate($refreshGrant);

            $this->setNewAccessToken($request, $result);

            return true;

        } catch(\Stormpath\Resource\ResourceError $re) {
            return false;
        }

    }

    private function responseUnauthenticated(Request $request)
    {
        if ($request->wantsJson()) {
            return response(null, 401);
        }

        return redirect()->route('stormpath.login');
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
