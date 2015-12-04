<?php


/*
 |--------------------------------------------------------------------------
 | Stormpath Laravel Routes
 |--------------------------------------------------------------------------
 |
 | These are all the routes that are possible within the application. Only
 | routes that are enabled via the Stormpath Laravel configuration file
 | will be loaded. Any of these routes can be overridden in by you.
 */

/*
 |--------------------------------------------------------------------------
 | Login Routes
 |--------------------------------------------------------------------------
 */
if (config('stormpath.web.login.enabled')) {
    $this->app->router->get( config('stormpath.web.login.uri'), ['as' => 'stormpath.login', 'uses' => 'Stormpath\Laravel\Http\Controllers\LoginController@getLogin'] );
    $this->app->router->post( config('stormpath.web.login.uri'), ['as' => 'stormpath.login', 'uses' => 'Stormpath\Laravel\Http\Controllers\LoginController@postLogin'] );
}

/*
 |--------------------------------------------------------------------------
 | Logout Routes
 |--------------------------------------------------------------------------
 */
if (config('stormpath.web.logout.enabled')) {
    $this->app->router->get( config('stormpath.web.logout.uri'), ['as' => 'stormpath.logout', 'uses' => 'Stormpath\Laravel\Http\Controllers\LoginController@getLogout'] );
    }

/*
 |--------------------------------------------------------------------------
 | Register Routes
 |--------------------------------------------------------------------------
 */
if (config('stormpath.web.register.enabled')) {
    $this->app->router->get( config('stormpath.web.register.uri'), ['as' => 'stormpath.register', 'uses' => 'Stormpath\Laravel\Http\Controllers\RegisterController@getRegister'] );
    $this->app->router->post( config('stormpath.web.register.uri'), ['as' => 'stormpath.register', 'uses' => 'Stormpath\Laravel\Http\Controllers\RegisterController@postRegister'] );
}
