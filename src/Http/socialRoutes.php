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
//    /*
//     |--------------------------------------------------------------------------
//     | Social Provider Routes
//     |--------------------------------------------------------------------------
//     */
//    if (config('stormpath.web.social.enabled')) {
//        $this->app->router->get(config('stormpath.web.login.uri') . '/facebook', ['as' => 'stormpath.login.facebook', 'uses' => 'Stormpath\Laravel\Http\Controllers\SocialController@facebook']);
//        $this->app->router->get(config('stormpath.web.login.uri') . '/google', ['as' => 'stormpath.login.google', 'uses' => 'Stormpath\Laravel\Http\Controllers\SocialController@google']);
//        $this->app->router->get(config('stormpath.web.login.uri') . '/linkedin', ['as' => 'stormpath.login.linkedin', 'uses' => 'Stormpath\Laravel\Http\Controllers\SocialController@linkedin']);
//    }

    /*
     |--------------------------------------------------------------------------
     | Social Callback Routes
     |--------------------------------------------------------------------------
     */
    if (config('stormpath.web.social.enabled')) {
        if (config('stormpath.web.social.facebook.enabled')) {
            $this->app->router->get(config('stormpath.web.social.facebook.uri'), ['as' => 'stormpath.callbacks.facebook', 'uses' => 'Stormpath\Laravel\Http\Controllers\SocialCallbackController@facebook']);
        }

        if (config('stormpath.web.social.google.enabled')) {
            $this->app->router->get(config('stormpath.web.social.google.uri'), ['as' => 'stormpath.callbacks.google', 'uses' => 'Stormpath\Laravel\Http\Controllers\SocialCallbackController@google']);
        }

        if (config('stormpath.web.social.github.enabled')) {
            $this->app->router->get(config('stormpath.web.social.github.uri'), ['as' => 'stormpath.callbacks.github', 'uses' => 'Stormpath\Laravel\Http\Controllers\SocialCallbackController@github']);
        }

        if (config('stormpath.web.social.linkedin.enabled')) {
            $this->app->router->get(config('stormpath.web.social.linkedin.uri'), ['as' => 'stormpath.callbacks.linkedin', 'uses' => 'Stormpath\Laravel\Http\Controllers\SocialCallbackController@linkedin']);
        }

    }
});