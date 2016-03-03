.. _quickstart:

Quickstart
==========

Now that we've got all the prerequisites out of the way, let's take a look at
some code!  Integrating Stormpath-Laravel into an application can take as little
as **1 minute**!

Environment Variables
---------------------
In the last section, we gathered our API credentials for the Stormpath API.
Now we'll configure our Laravel application to use them.

Open your ``.env`` file and add the following at the end:

.. code-block:: bash

  STORMPATH_CLIENT_APIKEY_ID={ID_FROM_API_CREDENTIALS}
  STORMPATH_CLIENT_APIKEY_SECRET={SECRET_FROM_API_CREDENTIALS}
  STORMPATH_APPLICATION_HREF={ID_OF_APPLICATION}

The ``STORMPATH_ID`` option should be a long, random string that was generated
and part of the API credentials file you downloaded.

The ``STORMPATH_SECRET`` option should be a long, random string that was generated
and part of the API credentials file you downloaded.

The ``STORMPATH_APPLICATION`` option requires you to specify your Stormpath Application
href, which can be found under your Application on the `Stormpath Applications`_
dashboard page.

.. note::

  The .env file should not be committed to your VCS



Initialize Stormpath-Laravel
----------------------------

After installing the Stormpath-Laravel package, you need to use add the service provider
to your app config. Open your ``config/app.php`` file and add the following:

.. code-block:: php

  'providers' => [
      ...
      Stormpath\Laravel\Support\StormpathLaravelServiceProvider::class,
      ...
  ]

The next thing is to publish the stormpath config file.  This can be done by running the following
in your terminal.

.. code-block:: bash

  php artisan vendor:publish


You will see a new file in your config folder named ``stormpath.php`` which has all the configuration options
for the package.

Testing It Out
--------------

If you followed the step above, you will now have fully functional
registration, login, and logout functionality active on your site!

Don't believe me?  Test it out!  Start up your web server now, and I'll
walk you through the basics:

- Navigate to ``/register``.  You will see a registration page.  Go ahead and
  enter some information.  You should be able to create a user account.  Once
  you've created a user account, you'll be automatically logged in, then
  redirected back to the root URL (``/``, by default).
- Navigate to ``/logout``.  You will now be logged out of your account, then
  redirected back to the root URL (``/``, by default).
- Navigate to ``/login``.  You will see a login page.  You can now re-enter
  your user credentials and log into the site again.

Wasn't that easy?!

.. note::

  You probably noticed that you couldn't register a user account without
  specifying a sufficiently strong password.  This is because, by default,
  Stormpath enforces certain password strength rules on your Stormpath
  Directories.

  If you'd like to change these password strength rules (*or disable them*),
  you can do so easily by visiting the `Stormpath dashboard`_, navigating to
  your user Directory, then changing the "Password Strength Policy".

Other Notes
===========

Running an API
--------------
If you are running this package for your API, We suggest that you run it on
a separate instance of laravel. This is so you can disable CSRF Verification
for requests. To disable CSRF verification, open your ``app/http/Kernel.php`` file
and comment out the ``\App\Http\Middleware\VerifyCsrfToken::class,`` line.

Caching
-------
This package now relies heavly on caching to improve response times. We cache a
number of items on the first page load and they are cached forever. If you make
any changes to your application through the Stormpath Dahsboard, you will need to
clear your laravel cache. Do this by running ``php artisan cache:clear`` from the
command line.  Your next request after doing this will take a little longer, but
the cache will be refreshed with the new settings.


.. _Stormpath applications: https://api.stormpath.com/v#!applications
.. _Stormpath dashboard: https://api.stormpath.com/ui/dashboard