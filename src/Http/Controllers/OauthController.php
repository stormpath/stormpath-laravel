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
use Stormpath\Authc\Api\OAuthRequestAuthenticator;
use Stormpath\Oauth\OauthGrantAuthenticationResult;

class OauthController extends Controller
{
    private $validGrantTypes = [
        'client_credentials',
        'password'
    ];

    public function getTokens(Request $request)
    {
        $grantType = $this->getGrantType($request);

        if(!$this->isValidGrantType($grantType)) {
            return $this->respondUnsupportedGrantType();
        }

        $method = $this->buildGrantTypeMethodName($grantType);

        return $this->{$method}($request);
    }

    private function doClientCredentialsGrantType($request)
    {
        try {
            $request = \Stormpath\Authc\Api\Request::createFromGlobals();
            $result = (new OAuthRequestAuthenticator(app('stormpath.application')))->authenticate($request);
            dd('here');

            $tokenResponse = $result->tokenResponse;
            return $tokenResponse->toJson();
        } catch(\Exception $e) {
            dd($e);
        }
    }

    private function doPasswordGrantType($request)
    {
        $passwordGrant = new \Stormpath\Oauth\PasswordGrantRequest($request->input('login'), $request->input('password'));
        $auth = new \Stormpath\Oauth\PasswordGrantAuthenticator(app('stormpath.application'));
        $result = $auth->authenticate($passwordGrant);

        if(!$result instanceof OauthGrantAuthenticationResult) {
            return $this->respondWithInvalidLogin();
        }

        return $this->respondWithAccessTokens($result);
    }

    private function respondUnsupportedGrantType()
    {
        return response()->json([
            'error' => 'unsupported_grant_type'
        ]);
    }

    private function getGrantType($request)
    {
        return $request->input('grant_type');
    }

    private function isValidGrantType($grantType)
    {


        if(!in_array($grantType, $this->validGrantTypes)) {
            return false;
        }

        return config("stormpath.web.oauth2.{$grantType}");
    }

    /**
     * @param $grantType
     * @return mixed|string
     */
    private function buildGrantTypeMethodName($grantType)
    {
        $method = str_replace('_', ' ', $grantType);
        $method = ucwords($method);
        $method = str_replace(' ', '', $method);
        $method = "do{$method}GrantType";
        return $method;
    }

    private function respondWithInvalidLogin()
    {
        return response()->json([
            'message' => 'Could not successfully log you in.',
            'error' => 'invalid_request'
        ]);
    }

    private function respondWithAccessTokens(OauthGrantAuthenticationResult $result)
    {
        return response()->json([
            'access_token' => $result->getAccessTokenString(),
            'expires_in' => $result->getExpiresIn(),
            'refresh_token' => $result->getRefreshTokenString(),
            'token_type' => 'Bearer'
        ]);
    }
}