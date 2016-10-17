.. _changelog:

Change Log
==========

All library changes, in descending order. something

Version 0.4.3
-------------

**Released on October 17, 2016.**

- Fix bug in login with undefined variable (Fixes #58)
- Document the helpers for application and client (Fixes #54)

Version 0.4.2
-------------

**Released on Sep 20, 2016.**

- Documentation Update for GTM


Version 0.4.1
-------------

**Released on June 23, 2016.**

- Documentation Updates
- Fixed bug where account store cache would give non-object exception
- Updated Configuration from stormpath validation to local validation
- Updated Configuration cookie path default.

Version 0.4.0
-------------

**Released on April 6, 2016.**

- Update to Produces Middleware
- Update to Auth Middleware for more reliable checking
- Increase Code Coverage
- JWT Decode fix in /me route
- Passing in X-STORMPATH-AGENT value into the User Agent
- IdSiteModel Bug Fix to throw exception if model does not exist
- File Cleanup
- Prevent laravel cookie encryption on access and refresh token cookies
- Bug Fix: Cookies now sent on JSON request when logging in

Version 0.3.1
-------------

**Released on March 17, 2016.**

- Added Social Login for LinkedIn.
- Added .gitattributes for cleaner install via composer
- Updated Documentation

Version 0.3.0
-------------

**Released on March 3, 2016.**

- Added Social Login for Google and Facebook.
- Did a lot of work to speed up the integration. This uses Laravel Cache
- Updated Documentation
- Added Me Endpoint (Fixes #5)
- Ability to expand resources on me endpoint
- Updated configuration file to follow closer to spec
- Ability to tie iOS sdk into integration

Version 0.2.0
-------------

**Released on January 28, 2016.**

- Added Events (thanks @Kryten0807)
- Added ability to create custom data during registration (thanks @Kryten0807) (Fixes #25)
- Update Documentation
- Fixed #28 to allow refreshing access_token
- Added tests for ID Site (Fixes #29)

Version 0.1.0
-------------

**Released on January 6, 2016.**

- Initial Stable Release
- Ability to get currently logged in user from app
- Update Documentation


Version 0.1.0.RC3
-----------------

**Released on January 4, 2016.**

- Added check in routes for Laravel 5.2
- Added options includes in composer for Laravel 5.2
- Added ID Site Support

Version 0.1.0.RC2
-----------------

**Released on December 23, 2015.**

- Added JSON Responses to Login, Register, Change Password Routes.
- Add ability to specify what Accept methods you want to allow via config

Version 0.1.0.RC1
-----------------

**Released on December 15, 2015.**

- Updated config file to follow spec
- Documentation updated to fix syntax issues
- Added Build Matrix and some other changes to Travis


Version 0.1.0.alpha2
--------------------

**Released on December 14, 2015.**

- Changes environment variables.
- Application now has to be the full url, not just ID.
- Old input values are added if form redirects back because of errors.
- Adding auto-deployment for docs!

Version 0.1.0.alpha1
--------------------

**Released on December 4, 2015.**

- First release!
- Basic functionality.
- Basic docs.
- Lots to do!

