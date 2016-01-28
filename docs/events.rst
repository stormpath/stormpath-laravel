.. _events:


Laravel Events
==============

This package provides a number of `events`_ allowing you to respond to user
actions as they happen.


Available Events
----------------

The following events (and their corresponding classes) are triggered during
Stormpath operations:

+-----------------------------------+------------------------------------------------------+
| Class                             | Description                                          |
+===================================+======================================================+
| ``UserIsRegistering``             | A potential new user has completed the registration  |
| (cancellable)                     | form and submitted it. The form data has passed      |
|                                   | validation.                                          |
+-----------------------------------+------------------------------------------------------+
| ``UserHasRegistered``             | A new user has registered.                           |
+-----------------------------------+------------------------------------------------------+
| ``UserIsLoggingIn``               | A user has completed the login form and submitted it.|
| (cancellable)                     | The form data has passed the initial validation, but |
|                                   | the user has not been authenticated yet.             |
+-----------------------------------+------------------------------------------------------+
| ``UserHasLoggedIn``               | A user has successfully logged in.                   |
+-----------------------------------+------------------------------------------------------+
| ``UserIsLoggingOut``              | A user has visited the ``logout`` URL but has not    |
| (cancellable)                     | been logged out yet.                                 |
+-----------------------------------+------------------------------------------------------+
| ``UserHasLoggedOut``              | A user has successfully logged out.                  |
+-----------------------------------+------------------------------------------------------+
| ``UserHasRequestedPasswordReset`` | A user has completed the password reset form and the |
|                                   | password reset email has been sent.                  |
+-----------------------------------+------------------------------------------------------+
| ``UserHasResetPassword``          | A user has successfully reset their password.        |
+-----------------------------------+------------------------------------------------------+

Note that all of the class names above are in the ``Stormpath\Laravel\Events``
namespace.


Listening for Events
--------------------

To register your listeners for these events, follow the
`Laravel documentation for Registering Events`_. For example, to register a
listener for the "registered" event, add the following to your
``EventServiceProvider``::

    protected $listen = [
        'Stormpath\Laravel\Events\UserHasRegistered' => [
            'App\Listeners\HandleNewUserRegistration',
        ],
    ];

where ``App\Listeners\HandleNewUserRegistration`` is a class in your application
which handles the event when it is fired. The listener class is defined
according to the `Laravel documentation on defining listeners`_.


Terminating Event Actions
-------------------------

There may be times when it is necessary to halt processing based on some
processing you are doing in your listener. To do this, simply return ``false``
from the ``handle`` method of your listener. Note that this will only have an
effect for the events that are marked as "cancellable" in the table above.

For example, maybe you want to prevent a user from registering if their first
name is "Bob". Your handler should look like this::

    public function handle(UserIsRegistering $event)
    {
        // get the form data that the user has submitted
        //
        $data = $event->getData();

        // check the givenName field
        //
        if ($data['givenName']=='Bob') {
            return false;
        }

        // the name is not Bob, so just carry on...
    }

This will abort the registration request. When this is done, a
``Stormpath\Laravel\Exceptions\ActionAbortedException`` will be thrown. In the
example above, you might catch that exception & redirect the user to a page that
says "No Bobs Allowed!"


Event Class Parameters
----------------------

Most of the event classes include data related to the event occurring. The
important data is retrieved via an accessor function.

+-----------------------------------+----------------+------------------------------------------------------+
| Class                             | Accessor       | Description                                          |
+===================================+================+======================================================+
| ``UserIsRegistering``             | ``getData``    | ``array`` - The form data entered by the user        |
+-----------------------------------+----------------+------------------------------------------------------+
| ``UserHasRegistered``             | ``getAccount`` | ``Stormpath\Resource\Account`` - The account object  |
|                                   |                | for the newly-registered user                        |
+-----------------------------------+----------------+------------------------------------------------------+
| ``UserIsLoggingIn``               | ``getData``    | ``array`` - The form data entered by the user        |
+-----------------------------------+----------------+------------------------------------------------------+
| ``UserHasLoggedIn``               | ``getAccount`` | ``Stormpath\Resource\Account`` - The account object  |
|                                   |                | for the user who has just logged in                  |
+-----------------------------------+----------------+------------------------------------------------------+
| ``UserIsLoggingOut``              | n/a            | No parameters                                        |
+-----------------------------------+----------------+------------------------------------------------------+
| ``UserHasLoggedOut``              | n/a            | No parameters                                        |
+-----------------------------------+----------------+------------------------------------------------------+
| ``UserHasRequestedPasswordReset`` | ``getData``    | ``array`` - The form data entered by the user        |
+-----------------------------------+----------------+------------------------------------------------------+
| ``UserHasResetPassword``          | n/a            | No parameters                                        |
+-----------------------------------+----------------+------------------------------------------------------+

So, for example, if you want to do something with a user who has just logged
out, the handler might look like this::

    public function handle(UserHasLoggedOut $event)
    {
        // get the user account
        //
        $user = $event->getAccount();

        // do something with the user...
    }



.. _events: https://laravel.com/docs/5.2/events
.. _Laravel documentation for Registering Events: https://laravel.com/docs/5.2/events#registering-events-and-listeners
.. _Laravel documentation on defining listeners: https://laravel.com/docs/5.2/events#defining-listeners
