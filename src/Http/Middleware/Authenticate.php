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
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if($request->headers->has('Authorization')) {
            $response = $this->validateWithAuthroizationHeader($request->headers->get('Authorization'));

            if($response === true) return $next($request);

            return response('', 401);
        }

        $isGuest = $this->isGuest($request);

        if ($isGuest) {
            return redirect()->guest(config('stormpath.web.login.uri'))
                ->withCookies([
                    cookie()->forget(config('stormpath.web.accessTokenCookie.name')),
                    cookie()->forget(config('stormpath.web.refreshTokenCookie.name'))
                ]);
        }

        return $next($request);
    }

    private function isGuest(Request $request)
    {

        if(!$request->hasCookie(config('stormpath.web.accessTokenCookie.name'))) {
            return true;
        }

        if(!$this->isValidToken($request)) {
            return true;
        }

        return false;

    }

    private function isValidToken($request)
    {
        $token = $request->cookie(config('stormpath.web.accessTokenCookie.name'));

        if(!is_string($token)) {
            $token = $token->getValue();
        }

        try {
            $result = (new \Stormpath\Oauth\VerifyAccessToken(app('stormpath.application')))->verify($token);

            return true;
        } catch (\Stormpath\Resource\ResourceError $re) {
            return $this->refreshToken($request);
        }

    }


    private function refreshToken($request)
    {

        $token = $request->cookie(config('stormpath.web.refreshTokenCookie.name'));

        if(!is_string($token)) {
            $token = $token->getValue();
        }

        $cookie = app(CookieJar::class);

        try {
            $refreshGrant = new \Stormpath\Oauth\RefreshGrantRequest($token);

            $auth = new \Stormpath\Oauth\RefreshGrantAuthenticator(app('stormpath.application'));
            $result = $auth->authenticate($refreshGrant);


            $cookie->queue(
                config('stormpath.web.accessTokenCookie.name'),
                $result->getAccessTokenString(),
                $result->getExpiresIn()
            );
            $cookie->queue(
                config('stormpath.web.refreshTokenCookie.name'),
                $result->getRefreshTokenString(),
                $result->getExpiresIn()
            );

            return true;

        } catch (\Stormpath\Resource\ResourceError $re) {
            return false;
        }
    }

    private function validateWithAuthroizationHeader($header)
    {
        $header = explode(' ', $header);
        $jwt = end($header);

        switch($validationStrategy = config('stormpath.web.oauth2.password.validationStrategy')) {
            case 'local' :
                return $this->localAuthroizationValidation($jwt);
            case 'stormpath' :
                return $this->stormpathAuthorizationValidation($jwt);
            default :
                throw new \InvalidArgumentException("Validation Strategy not supported. Provided Validation strategy is: {$validationStrategy}");
        }

    }

    private function localAuthroizationValidation($jwt)
    {
        \JWT::$leeway = 10;
        try {
            $jwt = \JWT::decode($jwt, config('stormpath.client.apiKey.secret'), ['HS256']);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function stormpathAuthorizationValidation($jwt)
    {
        try {
            $result = (new \Stormpath\Oauth\VerifyAccessToken(app('stormpath.application')))->verify($jwt);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
