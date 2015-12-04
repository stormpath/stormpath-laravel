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
        if(!session()->has(config('stormpath.web.accessTokenCookie.name'))) {
            return true;
        }

        if(!$this->isValidToken()) {
            return true;
        }

        return false;

    }

    private function isValidToken()
    {
        $token = session(config('stormpath.web.accessTokenCookie.name'));

        try {
            $result = (new \Stormpath\Oauth\VerifyAccessToken(app('stormpath.application')))->verify($token);

            return true;
        } catch (\Stormpath\Resource\ResourceError $re) {
            // TODO: Try to refresh the access token with the refreshTokenCookie
            return false;
        }

    }
}
