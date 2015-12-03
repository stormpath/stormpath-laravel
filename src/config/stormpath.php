<?php

return [

    "apiKey" => [
        "id" => env( 'STORMPATH_ID' ),
        "secret" => env( 'STORMPATH_SECRET' )
    ],

    "application" => env( 'STORMPATH_APPLICATION' ),

    "web" => [

        "oauth2" => [
            "enabled" => true,
            "uri" => "/oauth/token",

            "client_credentials" => [
                "enabled" => true,
                "accessToken" => [
                    "ttl" => 3600
                ]
            ],

            "password" => [
                "enabled" => true
            ]
        ],

        "accessTokenCookie" => [
            "name" => "access_token",
            "httpOnly" => true,
            "secure" => null,
            "path" => "/",
            "domain" => null
        ],

        "refreshTokenCookie" => [
            "name" => "refresh_token",
            "httpOnly" => true,
            "secure" => null,
            "path" => "/",
            "domain" => null
        ],

        "register" => [
            "enabled" => true,
            "uri" => "/register",
            "nextUri" => "/",
            "autoAuthorize" => false,
            "fields" => [
                "username" => [
                    "name" => "username",
                    "placeholder" => "Username",
                    "required" => false,
                    "type" => "text"
                ],
                "givenName" => [
                    "name" => "givenName",
                    "placeholder" => "First Name",
                    "required" => true,
                    "type" => "text"
                ],
                "middleName" => [
                    "name" => "middleName",
                    "placeholder" => "Middle Name",
                    "required" => false,
                    "type" => "text"
                ],
                "surname" => [
                    "name" => "surname",
                    "placeholder" => "Last Name",
                    "required" => true,
                    "type" => "text"
                ],
                "email" => [
                    "name" => "email",
                    "placeholder" => "Email",
                    "required" => true,
                    "type" => "email"
                ],
                "password" => [
                    "name" => "password",
                    "placeholder" => "Password",
                    "required" => true,
                    "type" => "password"
                ],
                "passwordConfirm" => [
                    "name" => "password_confirmation",
                    "placeholder" => "Confirm Password",
                    "required" => true,
                    "type" => "password"
                ]
            ],
            "fieldOrder" => [
                "username",
                "givenName",
                "middleName",
                "surname",
                "email",
                "password",
                "passwordConfirm"
            ],
            "view" => "stormpath::register"
        ],

        "verifyEmail" => [
            "enabled" => false,  // TODO: determine this from the API
            "uri" => "/verify",
            "nextUri" => "/",
            "view" => "stormpath::verify"
        ],

        "login" => [
            "enabled" => true,
            "autoLogin" => true,
            "uri" => "/login",
            "nextUri" => "/",
            "view" => "stormpath::login"
        ],

        "logout" => [
            "enabled" => false,
            "uri" => "/logout",
            "nextUri" => "/"
        ],

        "forgotPassword" => [
            "enabled" => false,
            "uri" => "/forgot",
            "view" => "stormpath::forgot-password",
            "nextUri" => "/login?status=forgot"
        ],

        "changePassword" => [
            "enabled" => false,
            "autoLogin" => false,
            "uri" => "/change",
            "nextUri" => "/login?status=reset",
            "view" => "stormpath::change-password",
            "errorUri" => "/forgot?status=invalid_sptoken"
        ],

        "idSite" => [
            "enabled" => false,
            "uri" => "/idSiteResult",
            "nextUri" => "/",
            "loginUri" => "",
            "forgotUri" => "/#/forgot",
            "registerUri" => "/#/register"
        ],

        "me" => [
            "enabled" => false,
            "uri" => "/me"
        ]

    ]
    
];