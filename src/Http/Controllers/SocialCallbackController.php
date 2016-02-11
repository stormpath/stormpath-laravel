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

namespace Stormpath\Laravel\Http\Controllers;

use Illuminate\Cookie\CookieJar;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Stormpath\Laravel\Http\Helpers\IdSiteSessionHelper;
use Stormpath\Resource\AccessToken;

class SocialCallbackController extends Controller
{
    private $application;
    private $cookieJar;

    public function __construct(CookieJar $cookieJar, $application = null)
    {
        $this->cookieJar = $cookieJar;
        $this->application = $application;

        if(null === $this->application)
            $this->application = app('stormpath.application');

    }

    public function facebook(Request $request)
    {
        try {
            $providerAccountRequest = new \Stormpath\Provider\FacebookProviderAccountRequest(array(
                "accessToken" => $request->get('access_token')
            ));

            $result = $this->application->getAccount($providerAccountRequest);

            $idSiteSession = new IdSiteSessionHelper();
            $accessTokens = $idSiteSession->create($result->account);

            $this->setCookies($accessTokens);

            return redirect()->to(config('stormpath.web.login.nextUri'));

        } catch (\Stormpath\Resource\ResourceError $re) {
            return redirect()->to(config('stormpath.web.login.uri'));
        }


    }

    public function google(Request $request)
    {
        try {
            $providerAccountRequest = new \Stormpath\Provider\GoogleProviderAccountRequest(array(
                "code" => $request->get('code')
            ));

            $result = $this->application->getAccount($providerAccountRequest);

            $idSiteSession = new IdSiteSessionHelper();
            $accessTokens = $idSiteSession->create($result->account);

            $this->setCookies($accessTokens);

            return redirect()->to(config('stormpath.web.login.nextUri'));
        } catch (\Stormpath\Resource\ResourceError $re) {
            return redirect()->to(config('stormpath.web.login.uri'));
        }

    }

    public function linkedin(Request $request)
    {
        try {
            $providerAccountRequest = new \Stormpath\Provider\LinkedInProviderAccountRequest(array(
                "code" => $request->get('code')
            ));

            $result = $this->application->getAccount($providerAccountRequest);

            $idSiteSession = new IdSiteSessionHelper();
            $accessTokens = $idSiteSession->create($result->account);

            $this->setCookies($accessTokens);

            return redirect()->to(config('stormpath.web.login.nextUri'));
        } catch (\Stormpath\Resource\ResourceError $re) {
            return redirect()->to(config('stormpath.web.login.uri'));
        }
    }

    private function setCookies(AccessToken $accessTokens)
    {
        $this->cookieJar->queue(
            cookie(
                config('stormpath.web.accessTokenCookie.name'),
                $accessTokens->access_token,
                3600,
                config('stormpath.web.accessTokenCookie.path'),
                config('stormpath.web.accessTokenCookie.domain'),
                config('stormpath.web.accessTokenCookie.secure'),
                config('stormpath.web.accessTokenCookie.httpOnly')
            )

        );

        $this->cookieJar->queue(
            cookie(
                config('stormpath.web.refreshTokenCookie.name'),
                $accessTokens->refresh_token,
                7200,
                config('stormpath.web.refreshTokenCookie.path'),
                config('stormpath.web.refreshTokenCookie.domain'),
                config('stormpath.web.refreshTokenCookie.secure'),
                config('stormpath.web.refreshTokenCookie.httpOnly')
            )

        );
    }

}
