.. _social_login:

Social Login
============

Do you want users to authenticate with a social provider, such as Facebook?
Stormpath provides integration with the following services:

* Facebook
* Google

In this guide we will cover Facebook, Google.


Facebook Login
--------------

To use Facebook Login you must create a Facebook Application, this is done
through their Developer site.  In the next few minutes I'll walk you through
*everything* you need to know to support Facebook login with your app.


Create a Facebook App
.....................

The first thing you need to do is log into the `Facebook Developer Site`_ and
create a new Facebook App.

You can do this by visiting the `Facebook Developer Site`_ and clicking the "Apps"
menu at the top of the screen, then select the "Create a New App" button.  You
should see something like the following:

.. image:: /_static/facebook-new-project.png

Go ahead and pick a "Display Name" (usually the name of your app), and choose a
category for your app.  Once you've done this, click the "Create App" button.


Specify Allowed URLs
....................

The next thing we need to do is tell Facebook what URLs we'll be using Facebook
Login from.

From the app dashboard page you're on, click the "Settings" tab in the left
menu, then click the "Add Platform" button near the bottom of the page.  When
prompted, select "Website" as your platform type.

In the "Site URL" box, enter your private and public root URLs.  This should be
something like ``"http://localhost:8000"`` or ``"http://mysite.com"``.  *If you
want to allow Facebook Login from multiple URLs (local development, production,
etc.) you can just click the "Add Platform" button again and enter another URL.*

Lastly, click the "Save Changes" button to save the changes.

Your settings should now look something like this:

.. image:: /_static/facebook-url-settings.png


Create a Facebook Directory
...........................

Next, we need to input the Facebook app credentials into Stormpath Directory.
This allows Stormpath to interact with the Facebook API on your behalf, which
automates all OAuth flows.

To do this, you need to visit the `Stormpath Admin Console`_ and create a new
directory.  When you click the "Create Directory" button you will choose
"Facebook" as the provider, and enter the following information about your
Facebook application:

- For the "Name" field, you can insert whatever name you want.
- For the "Facebook Client ID" field, insert your Facebook App ID which you got
  in the previous steps.
- For the "Facebook Client Secret" field, insert your Facebook Client Secret
  which you got in the previous steps.

Make sure to click "Create" to finish creating your directory.

Next, you need to hook your new Facebook Directory up to your Stormpath
Application.  To do this, visit the `Stormpath Admin Console`_, navigate to
Applications, and select your application from the list.

On your application page, click the "Account Stores" tab, then click the "Add
Account Store" button.  From the drop down list, select your newly created
Facebook Directory, then save your changes.

That's it!


Test it Out
...........

Now that you've plugged your Facebook credentials into Stormpath Laravel, social
login should already be working!

Open your laravel app in a browser, and try logging in by visiting the login page
(``/login``).  If you're using the default login page included with this
library, you should see the following:

.. image:: /_static/login-page-facebook.png

You now have a fancy new Facebook enabled login button!  Try logging in!  When
you click the new Facebook button you'll be redirected to Facebook, and
prompted to accept the permissions requested:

.. image:: /_static/login-page-facebook-permissions.png

After accepting permissions, you'll be immediately redirected back to your
website at the URL specified by ``redirectUrl`` in your app's config.

Simple, right?!


Google Login
------------

Integrating Google Login is very similar to Facebook.  You must create an application
in the Google Developer Console, then create a Directory in Stormpath which holds
settings for the Google application that you created.


Create a Google Project
.......................

The first thing you need to do is log into the `Google Developer Console`_ and
create a new Google Project.

You can do this by visiting the `Google Developer Console`_ and clicking the "Create
Project" button.  You should see something like the following:

.. image:: /_static/google-new-project.png

Go ahead and pick a "Project Name" (usually the name of your app), and
(*optionally*) a "Project ID".


Create OAuth Credentials
........................

The next thing we need to do is create a new OAuth client ID.  This is what
we'll use to handle user login with Google.

From your project, click the "APIs & auth" menu, then click on the "Credentials"
sub-menu.

You should see a big red button labeled "Create New Client ID" near the top of
the page -- click that.

You'll want to do several things here:

1. Select "Web application" for your "Application Type".
2. Remove everything from the "Authorized Javascript Origins" box.
3. Add the callback URI of your site (both publicly and locally) into the
   "Authorized Redirect URI" box.  This tells Google where to
   redirect users after they've logged in with Google.  The default callback
   URI for this library is ``/callbacks/google``.

In the end, your settings should look like this:

.. image:: /_static/google-oauth-settings.png

Once you've specified your settings, go ahead and click the "Create Client ID"
button.

Lastly, you'll want to take note of your "Client ID" and "Client Secret"
variables that should now be displayed on-screen.  We'll need these in the next
step.


Create a Google Directory
.........................

Next, we need to input the Google app credentials into Stormpath.  This allows
Stormpath to interact with the Google API on your behalf, which automates all
OAuth flows.

To do this, you need to visit the `Stormpath Admin Console`_ and create a new
directory from the Directories section.  When you click "Create Directory",
choose "Google" as the provider, and enter the following information about your
Google application:

- For the "Name" field, you can insert whatever name you want.
- For the "Google Client ID" field, insert your Google Client ID which you got
  in the previous steps.
- For the "Google Client Secret" field, insert your Google Client Secret
  which you got in the previous steps.
- For the "Google Authorized Redirect URI" field, insert your Google Redirect
  URL from the previous section. Be sure to *only enter the URI you're currently
  using*.  EG: If you're running your app in development mode, set it to your
  local URL, if you're running your app in production mode, set it to your
  production URL.

Lastly, be sure to click the "Save" button at the bottom of the page.

Next, you need to hook your new Google Directory up to your Stormpath
Application.  To do this, visit the Applications section and select your
application from the list.

On your application page, click the "Account Stores" tab, then click the "Add
Account Store" button.  From the drop down list, select your newly created
Google Directory, then save your changes.

That's it!


Test it Out
...........

Now that you've plugged your Google credentials into Stormpath Laravel, social
login should already be working!

Open your Laravel app in a browser, and try logging in by visiting the login page
(``/login``).  If you're using the default login page included with this
library, you should see the following:

.. image:: /_static/login-page-google.png

You now have a fancy new Google enabled login button!  Try logging in!  When you
click the new Google button you'll be redirected to Google, and prompted to
select your Google account:

.. image:: /_static/login-page-google-account.png

After selecting your account you'll then be prompted to accept any permissions,
then immediately redirected back to your website at the URL specified by
``redirectUrl`` in your app's settings.

Simple, right?!

LinkedIn Login
--------------

To use LinkedIn Login you must create a LinkedIn Application, this is done
through their Developer site.  In the next few minutes I'll walk you through
*everything* you need to know to support LinkedIn login with your app.


Create a LinkedIn App
.....................

The first thing you need to do is log into the `LinkedIn Developer Site`_ and
create a new LinkedIn App.

You can do this by visiting the `LinkedIn Developer Site`_ and clicking the "My Apps"
menu at the top of the screen, then select the "Create Application" button.  You
should see something like the following:

.. image:: /_static/linkedin-new-project.png

All fields on this page are required, so go ahead and fill them all out. Here is a sample of
what you can use to fill them in:

.. image:: /_static/linkedin-new-project-filled.png

Once you've done this, accept their terms and click submit.


Specify Allowed URLs
....................

The next thing we need to do is tell Linkedin what URLs they are allowed to send
the login details back to.

From the app dashboard page you're on, find the section for OAuth 2.0 and fill in
the ``Authorized Redirect URLs``. The default for the laravel integration is
``/callbacks/linkedin`` but this field needs to be a fully qualified url. Our example
uses ``http://localhost:8000`` for this. Once you fill in the field, Click on ``Add``

Next you will need to tell LinkedIn that you need email address from your users.
To do this, find the section on the same page called ``Default Application Permissions``
and make sure ``r_emailaddress`` is selected.

Lastly, click the "Save" button at the bottom to save the changes.

Your settings should now look something like this:

.. image:: /_static/linkedin-settings.png


Create a Linkedin Directory
...........................

Next, we need to input the Linkedin app credentials into Stormpath Directory.
This allows Stormpath to interact with the Linkedin API on your behalf, which
automates all OAuth flows.

To do this, you need to visit the `Stormpath Admin Console`_ and create a new
directory.  When you click the "Create Directory" button you will choose
"LinkedIn" as the provider, and enter the following information about your
Facebook application:

- For the "Name" field, you can insert whatever name you want.
- For the "LinkedIn Client ID" field, insert your LinkedIn Client ID which you got
  in the previous steps.
- For the "LinkedIn Client Secret" field, insert your LinkedIn Client Secret
  which you got in the previous steps.
- For the "linkedIn Authorized Redirect URI" field, insert the same url you set
  in the LinkedIn Developer Site

Make sure to click "Create" to finish creating your directory.

Next, you need to hook your new LinkedIn Directory up to your Stormpath
Application.  To do this, visit the `Stormpath Admin Console`_, navigate to
Applications, and select your application from the list.

On your application page, click the "Account Stores" tab, then click the "Add
Account Store" button.  From the drop down list, select your newly created
LinkedIn Directory, then save your changes.

That's it!


Test it Out
...........

Now that you've plugged your LinkedIn credentials into Stormpath , social
login should already be working!

Open your laravel app in a browser, and try logging in by visiting the login page
(``/login``).  If you're using the default login page included with this
library, you should see the following:

.. image:: /_static/login-page-linkedin.png

You now have a fancy new LinkedIn enabled login button!  Try logging in!  When
you click the new LinkedIn button you'll be redirected to LinkedIn, and
prompted to accept the permissions requested:

.. image:: /_static/login-page-linkedin-permissions.png

After accepting permissions, you'll be immediately redirected back to your
website at the URL specified by ``redirectUrl`` in your app's config.

Simple, right?!



.. _Stormpath Admin Console: https://api.stormpath.com
.. _Facebook Developer Site: https://developers.facebook.com/
.. _Google Developer Console: https://console.developers.google.com/project
.. _LinkedIn Developer Site: https://www.linkedin.com/developer/apps
