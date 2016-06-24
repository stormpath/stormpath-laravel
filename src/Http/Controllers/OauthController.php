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
use Stormpath\Authc\Api\OAuthClientCredentialsRequestAuthenticator;
use Stormpath\Authc\Api\OAuthRequestAuthenticator;
use Stormpath\Oauth\OauthGrantAuthenticationResult;

class OauthController extends Controller
{

    public function getTokens(Request $request)
    {
        $grantType = $this->getGrantType($request);

        switch($grantType) {
            case 'password' :
                return $this->doPasswordGrantType($request);
            // @codeCoverageIgnoreStart
            case 'client_credentials' :
                return $this->doClientCredentialsGrantType($request);
            // @codeCoverageIgnoreEnd
            case 'refresh_token' :
                return $this->doRefreshGrantType($request);
            default :
                return $this->respondUnsupportedGrantType();
        }

    }

    /** @codeCoverageIgnore */
    private function doClientCredentialsGrantType($request)
    {
        if(!config('stormpath.web.oauth2.client_credentials.enabled')) {
            return $this->respondUnsupportedGrantType();
        }
        try {
            $request = \Stormpath\Authc\Api\Request::createFromGlobals();
            $result = (new OAuthClientCredentialsRequestAuthenticator(app('stormpath.application')))->authenticate($request);

            $tokenResponse = json_decode($result->getAccessToken());
            return response()->json([
                'access_token' => $tokenResponse->access_token,
                'token_type' => $tokenResponse->token_type,
                'expires_in' => config('stormpath.web.oauth2.client_credentials.accessToken.ttl')
            ]);
        } catch(\Exception $e) {
            return $this->respondWithInvalidRequest($e->getMessage());
        }
    }

    private function doPasswordGrantType($request)
    {
        if(!config('stormpath.web.oauth2.password.enabled')) {
            return $this->respondUnsupportedGrantType();
        }
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
            'message' => 'The authorization grant type is not supported by the authorization server.',
            'error' => 'unsupported_grant_type'
        ], 400);
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
        ], 400);
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

    private function doRefreshGrantType($request)
    {
        if(null === $request->input('refresh_token')) {
            return $this->respondWithInvalidRequest('The refresh_token parameter is required.');
        }

        try {
            $refreshGrant = new \Stormpath\Oauth\RefreshGrantRequest($request->input('refresh_token'));

            $auth = new \Stormpath\Oauth\RefreshGrantAuthenticator(app('stormpath.application'));
            $result = $auth->authenticate($refreshGrant);
            return $this->respondWithAccessTokens($result);
        } catch (\Exception $e) {
            return $this->respondWithInvalidLogin($e);
        }

    }

    private function respondWithInvalidRequest($message = 'Invalid Request')
    {
        return response()->json([
            'message' => $message,
            'error' => 'invalid_request'
        ], 400);
    }
}