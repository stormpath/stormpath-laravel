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
use Event;
use Stormpath\Laravel\Exceptions\ActionAbortedException;
use Stormpath\Laravel\Events\UserHasRequestedPasswordReset;

class ForgotPasswordController extends Controller
{

    /**
     * @var Request
     */
    private $request;

    public function __construct(Request $request)
    {
        $this->middleware('stormpath.produces');

        $this->request = $request;
    }

    public function getForgotPassword()
    {
        if( config('stormpath.web.idSite.enabled') ) {
            return redirect(app('stormpath.application')->createIdSiteUrl(['path'=>config('stormpath.web.idSite.forgotUri'), 'callbackUri'=>route('stormpath.idSiteResponse')]));
        }
        $status = $this->request->get('status');
        return view( config('stormpath.web.forgotPassword.view'), compact('status') );
    }

    public function postForgotPassword(Request $request)
    {
        try {
            $input = $request->all();
            // we're about to post the "forgot password" request. Fire the
            // `UserHasRequestedPasswordReset` event
            //
            if (false===Event::fire(new UserHasRequestedPasswordReset(['email' => $input['email']]), [], true)) {
                throw new ActionAbortedException;
            }

            $application = app( 'stormpath.application' );
            $application->sendPasswordResetEmail($input['email']);


            if($request->wantsJson()) {
                return response(null, 200);
            }

            return redirect()
                ->to(config('stormpath.web.forgotPassword.nextUri'));

        } catch (ResourceError $re) {

            if($request->wantsJson()) {
                return response()->json([
                    'message' => 'Could not find an account with this email address',
                    'status' => $re->getStatus()
                ],400);
            };

            return redirect()
                ->to(config('stormpath.web.forgotPassword.uri'))
                ->withErrors(['errors'=>['Could not find an account with this email address']])
                ->withInput();
        }


    }
}
