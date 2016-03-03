.. _user_data:

User Data
=========


Current Logged In User
----------------------

If you are writing your own middleware functions, you will
likely want to use the account object.  If a user is logged in,
their account will be available on `app('stormpath.user')`.

Let's say we've defined a simple view that should simply display a user's email
address.  We can make use of the magical ``app('stormpath.user')`` context variable to
do this::

    Route::get('/email', ['middleware'=>['stormpath.auth']], function () {
      res.send('Your email address is: ' + app('stormpath.user')->email);
    });

The ``app('stormpath.user')`` context allows you to directly interact with the current
``user`` object.  This means you can perform *any* action on the ``user`` object
directly.  For a full list of actions, see the `Account Object`_ in the `Stormpath PHP SDK`_

Perhaps you want to change a user's ``givenName`` (*first name*).  You could
easily accomplish this with the following code::

    $user = app('stormpath.user');
    $user->givenName = 'Brian';
    $user->save();

As you can see above, you can directly modify ``user`` attributes, then
save any changes by running ``$user->save()``.


Custom Data
-----------

In addition to managing basic user fields, Stormpath also allows you to store
up to 10MB of JSON information with each user account!

Instead of defining a database table for users, and another database table for
user profile information -- with Stormpath, you don't need either!

Let's take a look at how easy it is to store custom data on a ``user``
model::

    $user = app('stormpath.user');
    // You can add fields
    $customData = $user->customData;
    $customData->somefield = 'somevalue';
    $customData->anotherfield = {'json': 'data'};
    $customData->woot = 10.202223;
    $customData->save();



As you can see above -- storing custom information on a ``user`` account is
extremely simple!

User Context
------------
This support is for front-end clients such as AngularJs. This endpoint allows the
front-end application to fetch the account object of the currently authenticated user.

We must provide this endpoint because, for security reasons, we don't allow the
client to store any information about the user. It must be fetched from the server
at runtime.

This route is enabled by default at the ``/me`` uri, but this endpoint can be changed
or disabled entirely with these options:::

    web:
      me:
        enabled: true
        uri: "/me"

The endpoint will always respond with ``Content-Type: application/json`` and the body
will be the JSON representation of the currently authenticated user.

By default, all linked resources will be removed from the object. However the
developer can opt-in to expansion through configuration. In this situation the
linked resource will be returned.


.. _Account Object: http://docs.stormpath.com/nodejs/api/account
.. _Stormpath PHP SDK: http://github.com/stormpath/stormpath-sdk-php