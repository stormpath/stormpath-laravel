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
use Stormpath\Resource\Account;

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
        if($request->has('accessToken')) {
            return $this->facebookAccessTokenLogin($request->get('accessToken'));
        }

        if($request->has('code')) {
            return $this->facebookCodeLogin($request->get('code'));
        }

        return redirect()->to(config('stormpath.web.login.uri'));

    }

    protected function facebookCodeLogin($code)
    {
        $provider = new \League\OAuth2\Client\Provider\Facebook([
            'clientId'          => config('stormpath.web.socialProviders.facebook.clientId'),
            'clientSecret'      => config('stormpath.web.socialProviders.facebook.clientSecret'),
            'redirectUri'       => url(config('stormpath.web.socialProviders.callbackRoot').'/facebook'),
            'graphApiVersion'   => 'v2.5',
        ]);

        $token = $provider->getAccessToken('authorization_code', [
            'code' => $code
        ]);

        return $this->facebookAccessTokenLogin($token->getToken());
    }

    protected function facebookAccessTokenLogin($token)
    {
        try {
            $providerAccountRequest = new \Stormpath\Provider\FacebookProviderAccountRequest(array(
                "accessToken" => $token
            ));

            $account = $this->sendProviderAccountRequest($providerAccountRequest);

            if(app('request')->wantsJson()) {
                return $this->respondWithAccount($account);
            }

            $this->setCookies($account);


            return redirect()->to(config('stormpath.web.login.nextUri'));

        } catch (\Stormpath\Resource\ResourceError $re) {
            redirect()->to(config('stormpath.web.login.uri'));
        }
    }

    public function google(Request $request)
    {
//        dd($request->get('code'));
        try {
            $providerAccountRequest = new \Stormpath\Provider\GoogleProviderAccountRequest(array(
                "code" => $request->get('code')
            ));

            $account = $this->sendProviderAccountRequest($providerAccountRequest);

            if(app('request')->wantsJson()) {
                return $this->respondWithAccount($account);
            }

            $this->setCookies($account);

            return redirect()->to(config('stormpath.web.login.nextUri'));
        } catch (\Stormpath\Resource\ResourceError $re) {
            return redirect()->to(config('stormpath.web.login.uri'));
        }

    }


    protected function sendProviderAccountRequest(ProviderAccountRequest $providerAccountRequest)
    {
        $result = $this->application->getAccount($providerAccountRequest);
        return $result->account;
    }

    protected function setCookies(Account $account)
    {
        $idSiteSession = new IdSiteSessionHelper();
        $accessTokens = $idSiteSession->create($account);

        $this->queueAccessToken($accessTokens->access_token);
        $this->queueRefreshToken($accessTokens->refresh_token);
    }

    private function respondWithAccount(Account $account)
    {
        $properties = ['account'=>[]];
        $blacklistProperties = [
            'providerData',
            'httpStatus',
            'createdAt',
            'modifiedAt'
        ];

        $propNames = $account->getPropertyNames();
        foreach($propNames as $prop) {
            if(in_array($prop, $blacklistProperties)) continue;
            $properties['account'][$prop] = $this->getPropertyValue($account, $prop);
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
