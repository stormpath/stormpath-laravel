<?php

namespace Stormpath\Laravel\Http\Traits;

trait AuthenticatesUser
{
    public function authenticate($user, $password)
    {
        $passwordGrant = new \Stormpath\Oauth\PasswordGrantRequest($user, $password);
        $auth = new \Stormpath\Oauth\PasswordGrantAuthenticator(app('stormpath.application'));
        $result = $auth->authenticate($passwordGrant);

        session([config('stormpath.web.accessTokenCookie.name') => $result->getAccessTokenString()]);
        session([config('stormpath.web.refreshTokenCookie.name') => $result->getRefreshTokenString()]);
    }
}