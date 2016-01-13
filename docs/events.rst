.. _events:


Laravel Events
==============

This package provides a number of events <https://laravel.com/docs/5.2/events>,
allowing you to respond to user actions as they happen.

Available Events
----------------

The following events (and their corresponding classes) are triggered during
Stormpath operations:

+--------------------------+-----------------------------------+------------------------------------------------------+
| Event                    | Class                             | Description                                          |
+==========================+===================================+======================================================+
| *registering*            | ``UserIsRegistering``             | A potential new user has completed the registration  |
|                          |                                   | form and submitted it. The form data has passed      |
|                          |                                   | validation.                                          |
+--------------------------+-----------------------------------+------------------------------------------------------+
| *registered*             | ``UserHasRegistered``             | A new user has registered.                           |
+--------------------------+-----------------------------------+------------------------------------------------------+
| *loggingIn*              | ``UserIsLoggingIn``               | A user has completed the login form and submitted it.|
|                          |                                   | The form data has passed the initial validation, but |
|                          |                                   | the user has not been authenticated yet.             |
+--------------------------+-----------------------------------+------------------------------------------------------+
| *loggedIn*               | ``UserHasLoggedIn``               | A user has successfully logged in.                   |
+--------------------------+-----------------------------------+------------------------------------------------------+
| *loggingOut*             | ``UserIsLoggingOut``              | A user has visited the ``logout`` URL but has not    |
|                          |                                   | been logged out yet.                                 |
+--------------------------+-----------------------------------+------------------------------------------------------+
| *loggedOut*              | ``UserHasLoggedOut``              | A user has successfully logged out.                  |
+--------------------------+-----------------------------------+------------------------------------------------------+
| *requestedPasswordReset* | ``UserHasRequestedPasswordReset`` | A user has completed the password reset form and the |
|                          |                                   | password reset email has been sent.                  |
+--------------------------+-----------------------------------+------------------------------------------------------+
| *resetPassword*          | ``UserHasResetPassword``          | A user has successfully reset their password.        |
+--------------------------+-----------------------------------+------------------------------------------------------+
