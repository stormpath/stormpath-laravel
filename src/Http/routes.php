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
