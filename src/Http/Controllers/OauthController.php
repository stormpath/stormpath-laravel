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

        switch($grantType) {
            case 'password' :
                return $this->doPasswordGrantType($request);
            case 'client_credentials' :
                return $this->doClientCredentialsGrantType($request);
            default :
                return $this->respondUnsupportedGrantType();
        }

    }

    private function doClientCredentialsGrantType($request)
    {
        try {
            $request = \Stormpath\Authc\Api\Request::createFromGlobals();
            $result = (new OAuthRequestAuthenticator(app('stormpath.application')))->authenticate($request);

            $tokenResponse = $result->tokenResponse;
            return $tokenResponse->toJson();
        } catch(\Exception $e) {
            dd($e);
        }
    }

    private function doPasswordGrantType($request)
    {
        try {
            $passwordGrant = new \Stormpath\Oauth\PasswordGrantRequest($request->input('username'), $request->input('password'));
            $auth = new \Stormpath\Oauth\PasswordGrantAuthenticator(app('stormpath.application'));
            $result = $auth->authenticate($passwordGrant);
            return $this->respondWithAccessTokens($result);
        } catch (\Exception $e) {
            return $this->respondWithInvalidLogin($e);
        }
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

    private function respondWithInvalidLogin($e)
    {
        return response()->json([
            'message' => $e->getMessage(),
            'error' => 'invalid_grant'
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