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

namespace Stormpath\Laravel\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Stormpath\Resource\ResourceError;

class ForgotPasswordController extends Controller
{

    /**
     * @var Request
     */
    private $request;

    public function __construct(Request $request)
    {

        $this->request = $request;
    }

    public function getForgotPassword()
    {
        $status = $this->request->get('status');
        return view( config('stormpath.web.forgotPassword.view'), compact('status') );
    }

    public function postForgotPassword(Request $request)
    {
        try {
            $application = app( 'stormpath.application' );
            $application->sendPasswordResetEmail($request->get('email'));

            return redirect()
                ->to(config('stormpath.web.forgotPassword.nextUri'));

        } catch (ResourceError $re) {
            return redirect()
                ->to(config('stormpath.web.forgotPassword.uri'))
                ->withErrors(['errors'=>['Could not find an account with this email address']])
                ->withInput();
        }


    }
}