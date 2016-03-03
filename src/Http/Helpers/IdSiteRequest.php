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

use Stormpath\Resource\Resource;

class IdSiteRequest extends Resource
{
    public function setStormpathToken($token) {
        $this->setProperty('token', $token);
        return $this;
    }

    public function setGrantType($type)
    {
        $this->setProperty('grant_type', $type);
        return $this;
    }

    public function getStormpathToken()
    {
        return $this->getProperty('token');
    }

    public function getGrantType()
    {
        return $this->getProperty('grant_type');
    }
}