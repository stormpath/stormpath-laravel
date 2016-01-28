.. _registration:


Registration
============

The registration feature of this library allows you to use Stormpath to create
new accounts in a Stormpath directory.  You can create traditional password-based accounts.

The registration option is enabled automatically and is available at this URL:

http://localhost:8000/register


Configuration Options
---------------------

This feature supports several options (which can be set in ``config/stormpath.php``.
This example shows what is possible, we will cover them in detail below:

.. code-block:: php

    [
      "web" => [
        "register" => [
          "enabled" => true,
          "uri" => "/signup",
          "nextUri" => "/",
          "fields" => [
            /* see next section for documentation */
          },
          "fieldOrder" => [ /* see next section */ ]
        }
      }
    }


Modifying Default Fields
------------------------

The registration form will render these fields by default, and they will be
required by the user:

* givenName
* surname
* email
* password

While email and password will always be required, you do not need to require
givenName and surname.  These can be configured as optional fields, or omitted
entirely.  We'll cover each use case in detail.

You can also specify your own custom fields. In this case, the custom data will
be saved with the account and will be accessible via the ``customData`` property 
of the account.

Password Strength Rules
-----------------------

Stormpath supports complex password strength rules, such as number of letters
or special characters required.  These settings are controlled on a directory
basis.  If you want to modify the password strength rules for your application
you should use the `Stormpath Admin Console`_ to find the directory that is mapped
to your application, and modify it's password policy.

For more information see `Account Password Strength Policy`_.


Email Verification
------------------

We **highly** recommend that you use email verification, as it adds a layer
of security to your site (it makes it harder for bots to create spam accounts).

One of our favorite Stormpath features is email verification.  When this workflow
is enabled on the directory, we will send the new account an email with a link
that they must click on in order to verify their account.  When they click on
that link they will need to be directed to this URL:

http://localhost:8000/verify?sptoken=TOKEN

We have to configure our directory in order for this to happen. Use the
`Stormpath Admin Console`_ to find the directory of your application, then
go into the Workflows section.  In there you will find the email verification
workflow, which should be enabled by default (enable it if not).  Then modify
the template of the email to use this value for the `Link Base URL`:

.. code-block:: sh

    http://localhost:8000/verify

When the user arrives on the verification URL, we will verify that their email
link is valid and hasn't already been used.  If the link is valid we will redirect
them to the login page.  If there is a problem with the link we provide a form
that allows them to ask for a new link.


Auto Login
----------

If you are *not* using email verificaion (not recommended) you may log users in
automatically when they register.  This can be achieved with this config::

    [
      "register" => [
        "autoLogin" => true,
        "nextUri" => "/"
      ]
    ]

By default the nextUri is to the `/` page, but you can modify this.



.. _Stormpath Admin Console: https://api.stormpath.com
.. _Account Password Strength Policy: https://docs.stormpath.com/rest/product-guide/#account-password-strength-policy
