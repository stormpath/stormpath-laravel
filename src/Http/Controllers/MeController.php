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
            return response('', 401);
        }
    }

    private function respondWithAccount(Account $account)
    {
        $properties = ['account'=>[]];
        $config = config('stormpath.web.me.expand');
        $whiteListResources = [];
        foreach($config as $item=>$value) {
            if($value == true) {
                $whiteListResources[] = $item;
            }
        }

        $propNames = $account->getPropertyNames();
        foreach($propNames as $prop) {
            $property = $this->getPropertyValue($account, $prop);

            if(is_object($property) && !in_array($prop, $whiteListResources)) {
                continue;
            }

            $properties['account'][$prop] = $property;
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

        $jwt = \JWT::decode($accessToken, config('stormpath.client.apiKey.secret'), ['HS256']);

        $expandsArray = [];
        $expands = config('stormpath.web.me.expand');
        foreach($expands as $key=>$value) {
            if($value == false) continue;
            $expandsArray[] = $key;
        }
        $toExpand = [];
        if(count($expandsArray) > 0) {
            $toExpand = ['expand' => implode(',',$expandsArray)];
        }

        $account = \Stormpath\Resource\Account::get($jwt->sub, $toExpand);
        return $account;
    }
}