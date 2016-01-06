.. _login:


Login
=====

Login is enabled out of the box for this package.  By default the login page
will be available at this URL:

http://localhost:8000/login

If the login attempt is successful, we will send the user to the Next URI
and create the proper session cookies.


Next URI
--------

The form will render with two fields for username and password, and this form
will be posted to ``/login``.  If login is successful, we will redirect the user
to ``/``.  If you wish to change this, use the ``nextUri`` config option in ``config/stormpath.php``::

    [
      "web" => [
        "login" => [
          "enabled" => true,
          "nextUri" => "/dashboard"
        ]
      ]
    ]



Using ID Site
-------------

Stormpath provides a hosted login application, known as ID Site.  This feature
allows you to redirect the user to our hosted application.  When the user
authenticates, they will be redirected back to your application with an identiy
assertion.

This feature is useful if you don't want to modify your application to serve
web pages or single page apps, and would rather have that hosted somewhere else.

ID site looks like this:

.. image:: /_static/id-site-login.png

> Currently this is the only way to allow for social login within the package

For more information about how to use and customize the ID site, please see
this documentation:

http://docs.stormpath.com/guides/using-id-site/


ID Site Configuration
.....................

If you wish to use the ID Site feature, you will need to log in to the
`Stormpath Admin Console`_ and configure the settings.  You need to change the
**Authorized Redirect Uri** setting and set it to
``http://localhost:8000/idSiteResult``

Then you want to enable ID Site in your express configuration::

    [
      "web" => [
        "idSite" => [
          "enabled" => true,
          "uri" => "/idSiteResult"    // default setting
          "nextUri" => "/"            // default setting
        ]
      ]
    ]

When ID Site is enabled, any request for ``/login`` or ``/register`` will cause a
redirect to ID Site.  When the user is finished at ID Site they will be
redirected to `/idSiteResult` on your application.  Our library will handle
this request, and then redirect the user to the ``nextUri``.


.. _Stormpath Admin Console: https://api.stormpath.com