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
use Stormpath\Laravel\Http\Traits\Cookies;
use Stormpath\Provider\ProviderAccountRequest;
use Stormpath\Resource\AccessToken;

class SocialCallbackController extends Controller
{
    use Cookies;

    private $application;

    public function __construct($application = null)
    {
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

            $this->sendProviderAccountRequest($providerAccountRequest);


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

            $this->sendProviderAccountRequest($providerAccountRequest);

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

            $this->sendProviderAccountRequest($providerAccountRequest);

            return redirect()->to(config('stormpath.web.login.nextUri'));
        } catch (\Stormpath\Resource\ResourceError $re) {
            return redirect()->to(config('stormpath.web.login.uri'));
        }
    }

    public function sendProviderAccountRequest(ProviderAccountRequest $providerAccountRequest)
    {
        $result = $this->application->getAccount($providerAccountRequest);

        $idSiteSession = new IdSiteSessionHelper();
        $accessTokens = $idSiteSession->create($result->account);

        $this->queueAccessToken($accessTokens->access_token);
        $this->queueRefreshToken($accessTokens->refresh_token);
    }

}
