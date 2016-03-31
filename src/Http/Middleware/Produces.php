<?php
/*
 * Copyright 2015 Stormpath, Inc.
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

namespace Stormpath\Laravel\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Cookie\CookieJar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class Produces
{
    /** @var array Array of allowed Accept Headers */
    private $produces = [];

    /** @var array Array of Accept Headers the integration knows how to respond to  */
    private $systemProduces = [
        'application/json',
        'text/html'
    ];



    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $this->produces = config('stormpath.web.produces');
        $acceptHeader = explode(',',$request->header('Accept'));
        $approvedProduces = array_intersect($this->systemProduces, $this->produces);

        if(!$this->hasApprovedProduces($approvedProduces)) {
            return $this->respondNotAcceptable('The system does not know how to respond to any accept headers defined.');
        }

        if(in_array('*/*', $acceptHeader)) {
            $request->headers->remove('Accept');
            $request->headers->set('Accept', $this->produces[0]);
            return $next($request);
        }

        if(!$this->acceptHeaderIsApproved($acceptHeader, $approvedProduces)) {
            return $this->respondNotAcceptable('Accept Header is not allowed.');
        }

        return $next($request);
    }

    private function respondNotAcceptable($message)
    {
        return response($message, 406);
    }

    /**
     * @param $approvedProduces
     * @return bool
     */
    private function hasApprovedProduces($approvedProduces)
    {
        return !! count($approvedProduces);
    }

    /**
     * @param $acceptHeader
     * @param $approvedProduces
     * @return int
     */
    private function acceptHeaderIsApproved($acceptHeader, $approvedProduces)
    {
        return !! count(array_intersect((array)$acceptHeader, $approvedProduces));
    }


}