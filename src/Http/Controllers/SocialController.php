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

use Illuminate\Routing\Controller;

class SocialController extends Controller
{

    public function google()
    {
        $provider = new \League\OAuth2\Client\Provider\Google([
            'clientId'          => config('stormpath.web.socialProviders.google.clientId'),
            'clientSecret'      => config('stormpath.web.socialProviders.google.clientSecret'),
            'redirectUri'       => config('stormpath.web.socialProviders.google.callbackUri')
        ]);

        $authUrl = $provider->getAuthorizationurl();

        return redirect()->to($authUrl);
    }

    public function facebook()
    {
        $provider = new \League\OAuth2\Client\Provider\Facebook([
            'clientId'          => config('stormpath.web.socialProviders.facebook.clientId'),
            'clientSecret'      => config('stormpath.web.socialProviders.facebook.clientSecret'),
            'redirectUri'       => url(config('stormpath.web.socialProviders.callbackRoot').'/facebook'),
            'graphApiVersion'   => 'v2.5',
        ]);

        $authUrl = $provider->getAuthorizationurl([
            'scope' => ['email']
        ]);

        return redirect()->to($authUrl);
    }

    public function linkedin()
    {
        $provider = new \League\OAuth2\Client\Provider\LinkedIn([
            'clientId'          => config('stormpath.web.socialProviders.linkedin.clientId'),
            'clientSecret'      => config('stormpath.web.socialProviders.linkedin.clientSecret'),
            'redirectUri'       => config('stormpath.web.socialProviders.linkedin.callbackUri')
        ]);

        $authUrl = $provider->getAuthorizationurl();

        return redirect()->to($authUrl);
    }
}