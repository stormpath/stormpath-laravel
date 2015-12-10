<?php
/*
 * Copyright 2015 Stormpath, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

return [

    "client" => [
        "apiKey" => [
            "id" => env( 'STORMPATH_CLIENT_APIKEY_ID' ),
            "secret" => env( 'STORMPATH_CLIENT_APIKEY_SECRET' )
        ],
        "baseUrl" => "https://api.stormpath.com/v1",
        "authenticationScheme" => "SAUTHC1"
    ],

    "application" => [
        "name" => "",
        "href" => env( 'STORMPATH_APPLICATION_HREF' )
    ],

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
            "enabled" => false,
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
            "enabled" => true,
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