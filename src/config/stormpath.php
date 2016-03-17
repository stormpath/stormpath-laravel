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
                "enabled" => true,
                "validationStrategy" => "stormpath"
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

        "produces" => [
          "text/html",
          "application/json"
        ],

        "register" => [
            "enabled" => true,
            "uri" => "/register",
            "nextUri" => "/",
            "autoAuthorize" => false,
            "form" => [
                "fields" => [
                    "givenName" => [
                        "enabled" => true,
                        "label" => "First Name",
                        "placeholder" => "First Name",
                        "required" => true,
                        "type" => "text"
                    ],
                    "middleName" => [
                        "enabled" => false,
                        "label" => "Middle Name",
                        "placeholder" => "Middle Name",
                        "required" => true,
                        "type" => "text"
                    ],
                    "surname" => [
                        "enabled" => true,
                        "label" => "Last Name",
                        "placeholder" => "Last Name",
                        "required" => true,
                        "type" => "text"
                    ],
                    "username" => [
                        "enabled" => false,
                        "label" => "Username",
                        "placeholder" => "Username",
                        "required" => false,
                        "type" => "text"
                    ],
                    "email" => [
                        "enabled" => true,
                        "label" => "Email",
                        "placeholder" => "Email",
                        "required" => true,
                        "type" => "email"
                    ],
                    "password" => [
                        "enabled" => true,
                        "label" => "Password",
                        "placeholder" => "Password",
                        "required" => true,
                        "type" => "password"
                    ],
                    "confirmPassword" => [
                        "enabled" => false,
                        "label" => "Confirm Password",
                        "placeholder" => "Confirm Password",
                        "required" => true,
                        "type" => "password"
                    ]
                ]
            ],
            "fieldOrder" => [
                "givenName",
                "middleName",
                "surname",
                "username",
                "email",
                "password",
                "confirmPassword"
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
            "enabled" => null,
            "uri" => "/forgot",
            "view" => "stormpath::forgot-password",
            "nextUri" => "/login?status=forgot"
        ],

        "changePassword" => [
            "enabled" => null,
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

        "social" => [
            "facebook" => [
                "uri" => "/callbacks/facebook",
                "scope" => "email"
            ],
            "github" => [
                "uri" => "/callbacks/github",
                "scope" => "user:email"
            ],
            "google" => [
                "uri" => "/callbacks/google",
                "scope" => "email, profile"
            ],
            "linkedin" => [
                "uri" => "/callbacks/linkedin",
                "scope" => "r_basicprofile r_emailaddress"
            ]
        ],

        "me" => [
            "enabled" => true,
            "uri" => "/me",
            "expand" => [
                "apiKeys" => false,
                "applications" => false,
                "customData" => false,
                "directory" => false,
                "groupMemberships" => false,
                "groups" => false,
                "providerData" => false,
                "tenant" => false
            ]
        ]

    ]
    
];