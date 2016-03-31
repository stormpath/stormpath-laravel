<?php
/*
 * Copyright 2016 Stormpath, Inc.
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

namespace Stormpath\Laravel\Http\Traits;

trait Cookies
{
    public function queueAccessToken($accessToken)
    {
        $cookieJar = app('cookie');

        $cookie = $this->makeAccessTokenCookie($accessToken);

        $cookieJar->queue($cookie);
    }

    public function makeAccessTokenCookie($accessToken)
    {
        return cookie(
            config('stormpath.web.accessTokenCookie.name'),
            $accessToken,
            $this->getExpiresTime('accessToken') / 60,
            config('stormpath.web.accessTokenCookie.path'),
            config('stormpath.web.accessTokenCookie.domain'),
            config('stormpath.web.accessTokenCookie.secure'),
            config('stormpath.web.accessTokenCookie.httpOnly')
        );
    }

    public function queueRefreshToken($refreshToken)
    {
        $cookieJar = app('cookie');

        $cookie = $this->makeRefreshTokenCookie($refreshToken);

        $cookieJar->queue($cookie);
    }

    public function makeRefreshTokenCookie($refreshToken)
    {
        return cookie(
            config('stormpath.web.refreshTokenCookie.name'),
            $refreshToken,
            $this->getExpiresTime('refreshToken') / 60,
            config('stormpath.web.refreshTokenCookie.path'),
            config('stormpath.web.refreshTokenCookie.domain'),
            config('stormpath.web.refreshTokenCookie.secure'),
            config('stormpath.web.refreshTokenCookie.httpOnly')
        );
    }

    private function getExpiresTime($type = 'accessToken')
    {
        $application = app('stormpath.application');

        $policy = $application->oauthPolicy;
        $methodName = 'get' . ucfirst($type) . 'Ttl';

        $policy->setOptions([]);

        $time = $policy->{$methodName}(['expand'=>'tokenEndpoint']);

        $converter = new \Bretterer\IsoDurationConverter\DurationParser();

        $seconds = $converter->parse($time);

        return $seconds;

    }
}