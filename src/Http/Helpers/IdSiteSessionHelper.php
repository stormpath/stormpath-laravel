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

namespace Stormpath\Laravel\Http\Helpers;

use Stormpath\Laravel\Exceptions\SocialLoginException;
use Stormpath\Stormpath;

class IdSiteSessionHelper
{

    public function create(\Stormpath\Resource\Account $account)
    {
        $refreshTokenCookieConfig = config('stormpath.web.refreshTokenCookie');
        $application = app('stormpath.application');
        try {
            $jwt = \JWT::encode([
                'sub' => $account->href,
                'iat' => time()-1,
                'status' => 'AUTHENTICATED',
                'iss' => $application->href,
                'aud' => config('stormpath.client.apiKey.id')
            ], config('stormpath.client.apiKey.secret'), 'HS256');

            $idSiteRequest = new IdSiteRequest();
            $idSiteRequest->stormpathToken = $jwt;
            $idSiteRequest->grantType = 'stormpath_token';

            return app('stormpath.client')->getDataStore()->create($application->href . '/oauth/token', $idSiteRequest, Stormpath::ACCESS_TOKEN);


        } catch (\Exception $e) {
            throw new SocialLoginException($e->getMessage());
        }

    }



}