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


/*
 |--------------------------------------------------------------------------
 | Stormpath Laravel Routes
 |--------------------------------------------------------------------------
 |
 | These are all the routes that are possible within the application. Only
 | routes that are enabled via the Stormpath Laravel configuration file
 | will be loaded. Any of these routes can be overridden in by you.
 */

$laravel = app();
$version = $laravel::VERSION;

$middleware = [];
if(version_compare($version, "5.2.0" , ">=")) {
    $middleware = ['middleware' => ['web']];
}

$this->app->router->group($middleware, function() {
    /*
     |--------------------------------------------------------------------------
     | Login Routes
     |--------------------------------------------------------------------------
     */
    if (config('stormpath.web.login.enabled')) {
        $this->app->router->get(config('stormpath.web.login.uri'), ['middleware' => 'stormpath.guest', 'as' => 'stormpath.login', 'uses' => 'Stormpath\Laravel\Http\Controllers\LoginController@getLogin']);
        $this->app->router->post(config('stormpath.web.login.uri'), ['middleware' => 'stormpath.guest', 'as' => 'stormpath.login', 'uses' => 'Stormpath\Laravel\Http\Controllers\LoginController@postLogin']);
    }

    /*
     |--------------------------------------------------------------------------
     | Logout Routes
     |--------------------------------------------------------------------------
     */
    if (config('stormpath.web.logout.enabled')) {
        $this->app->router->get(config('stormpath.web.logout.uri'), ['as' => 'stormpath.logout', 'uses' => 'Stormpath\Laravel\Http\Controllers\LoginController@getLogout']);
    }

    /*
     |--------------------------------------------------------------------------
     | Register Routes
     |--------------------------------------------------------------------------
     */
    if (config('stormpath.web.register.enabled')) {
        $this->app->router->get(config('stormpath.web.register.uri'), ['middleware' => 'stormpath.guest', 'as' => 'stormpath.register', 'uses' => 'Stormpath\Laravel\Http\Controllers\RegisterController@getRegister']);
        $this->app->router->post(config('stormpath.web.register.uri'), ['middleware' => 'stormpath.guest', 'as' => 'stormpath.register', 'uses' => 'Stormpath\Laravel\Http\Controllers\RegisterController@postRegister']);
    }

    /*
     |--------------------------------------------------------------------------
     | Forgot Password Routes
     |--------------------------------------------------------------------------
     */
    if (config('stormpath.web.forgotPassword.enabled')) {
        $this->app->router->get(config('stormpath.web.forgotPassword.uri'), ['middleware' => 'stormpath.guest', 'as' => 'stormpath.forgotPassword', 'uses' => 'Stormpath\Laravel\Http\Controllers\ForgotPasswordController@getForgotPassword']);
        $this->app->router->post(config('stormpath.web.forgotPassword.uri'), ['middleware' => 'stormpath.guest', 'as' => 'stormpath.forgotPassword', 'uses' => 'Stormpath\Laravel\Http\Controllers\ForgotPasswordController@postForgotPassword']);
    }

    /*
     |--------------------------------------------------------------------------
     | Change Password Routes
     |--------------------------------------------------------------------------
     */
    if (config('stormpath.web.changePassword.enabled')) {
        $this->app->router->get(config('stormpath.web.changePassword.uri'), ['middleware' => 'stormpath.guest', 'as' => 'stormpath.changePassword', 'uses' => 'Stormpath\Laravel\Http\Controllers\ChangePasswordController@getChangePassword']);
        $this->app->router->post(config('stormpath.web.changePassword.uri'), ['middleware' => 'stormpath.guest', 'as' => 'stormpath.changePassword', 'uses' => 'Stormpath\Laravel\Http\Controllers\ChangePasswordController@postChangePassword']);
    }

    /*
     |--------------------------------------------------------------------------
     | ID Site Response Route
     |--------------------------------------------------------------------------
     */
    if (config('stormpath.web.idSite.enabled')) {
        $this->app->router->get(config('stormpath.web.idSite.uri'), ['as' => 'stormpath.idSiteResponse', 'uses' => 'Stormpath\Laravel\Http\Controllers\IdSiteController@response']);
    }

    /*
     |--------------------------------------------------------------------------
     | Social Provider Routes
     |--------------------------------------------------------------------------
     */
    if (config('stormpath.web.socialProviders.enabled')) {
        $this->app->router->get(config('stormpath.web.login.uri') . '/facebook', ['as' => 'stormpath.login.facebook', 'uses' => 'Stormpath\Laravel\Http\Controllers\SocialController@facebook']);
        $this->app->router->get(config('stormpath.web.login.uri') . '/google', ['as' => 'stormpath.login.google', 'uses' => 'Stormpath\Laravel\Http\Controllers\SocialController@google']);
        $this->app->router->get(config('stormpath.web.login.uri') . '/linkedin', ['as' => 'stormpath.login.linkedin', 'uses' => 'Stormpath\Laravel\Http\Controllers\SocialController@linkedin']);
    }

    /*
     |--------------------------------------------------------------------------
     | Social Callback Routes
     |--------------------------------------------------------------------------
     */
    if (config('stormpath.web.socialProviders.enabled')) {
        $this->app->router->get(config('stormpath.web.socialProviders.callbackRoot') . '/facebook', ['as' => 'stormpath.callbacks.facebook', 'uses' => 'Stormpath\Laravel\Http\Controllers\SocialCallbackController@facebook']);
        $this->app->router->get(config('stormpath.web.socialProviders.callbackRoot') . '/linkedin', ['as' => 'stormpath.callbacks.linkedin', 'uses' => 'Stormpath\Laravel\Http\Controllers\SocialCallbackController@linkedin']);
        $this->app->router->get(config('stormpath.web.socialProviders.callbackRoot') . '/google', ['as' => 'stormpath.callbacks.google', 'uses' => 'Stormpath\Laravel\Http\Controllers\SocialCallbackController@google']);
    }

});
