<?php

namespace Stormpath\Laravel\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;

class Authenticate
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
        $isGuest = $this->isGuest();

        if ($isGuest) {
            return redirect()->guest(config('stormpath.web.login.uri'));
        }

        return $next($request);
    }

    private function isGuest()
    {
        return !session()->has(config('stormpath.web.accessTokenCookie.name'));
    }
}
