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
$laravel = app();
$version = $laravel::VERSION;

$middleware = [];
if(version_compare($version, "5.2.0" , ">=")) {
    $middleware = ['middleware' => ['web']];
}

$this->app->router->group($middleware, function() {
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