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

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Stormpath\Laravel\Http\Helpers\IdSiteSessionHelper;

class SocialCallbackController extends Controller
{
    private $application;

    public function __construct()
    {
        $this->application = app('stormpath.application');
    }

    public function facebook(Request $request)
    {
        $providerAccountRequest = new \Stormpath\Provider\FacebookProviderAccountRequest(array(
            "accessToken" => $request->get('access_token')
        ));

        $result = $this->application->getAccount($providerAccountRequest);

        $idSiteSession = new IdSiteSessionHelper();
        $session = $idSiteSession->create($result->account);

    }

    public function google(Request $request)
    {
        try {
            $providerAccountRequest = new \Stormpath\Provider\GoogleProviderAccountRequest(array(
                "code" => $request->get('code')
            ));

            $result = $this->application->getAccount($providerAccountRequest);

            dd($result);
        } catch (\Stormpath\Resource\ResourceError $re) {
            dd($re);
        }
//        $idSiteSession = new IdSiteSessionHelper();
//        $session = $idSiteSession->create($result->account);

    }

}
