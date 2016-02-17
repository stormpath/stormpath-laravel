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
use Stormpath\Resource\Account;
use Stormpath\Stormpath;

class MeController extends Controller
{

    public function getMe(Request $request)
    {
        $accessToken = $request->cookies->get(config('stormpath.web.accessTokenCookie.name'));

        if($request->headers->has('Authorization')) {
            $token = explode(' ', $request->headers->get('Authorization'));
            $accessToken = end($token);
        }

        try {
            $account = $this->getAccountFromAccessToken($accessToken);
            return $this->respondWithAccount($account);
        } catch (\Exception $e) {
            dd($e);
            return response('', 401);
        }
    }

    private function respondWithAccount(Account $account)
    {
        $properties = ['account'=>[]];
        $blacklistProperties = [
            'httpStatus',
            'account',
            'applications',
            'apiKeys',
            'emailVerificationToken',
            'providerData'
        ];

        $propNames = $account->getPropertyNames();
        foreach($propNames as $prop) {
            if(in_array($prop, $blacklistProperties)) continue;
            if(is_object($account->{$prop})) continue;

            $properties['account'][$prop] = $this->getPropertyValue($account, $prop);
        }
        return response()->json($properties);
    }

    private function getPropertyValue($account, $prop)
    {
        $value = null;
        try {
            $value = $account->getProperty($prop);
        } catch (\Exception $e) {
            return null;
        }

        return $value;

    }

    private function getAccountFromAccessToken($accessToken)
    {
        \JWT::$leeway = 10;

        $jwt = \JWT::decode($accessToken, config('stormpath.config.apiKey.secret'), ['HS256']);


        $account = \Stormpath\Resource\Account::get($jwt->sub);
        return $account;
    }
}