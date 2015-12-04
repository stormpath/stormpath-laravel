<?php

namespace Stormpath\Laravel\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;

class RedirectIfAuthenticated
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($this->isAuthenticated()) {
            return redirect('/');
        }

        return $next($request);
    }


    public function isAuthenticated()
    {
        if(!session()->has(config('stormpath.web.accessTokenCookie.name'))) {
            return false;
        }

        return true;
    }
}
