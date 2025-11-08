<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Age Rating Guidelines
    |--------------------------------------------------------------------------
    |
    | This refers to the ID of the age rating guidelines page. By specifying the
    | page ID, you can easily manage and update the age rating guidelines link.
    | Setting it to null will disable the age rating guidelines feature.
    |
    */

    'age_ratings' => env('PAGE_AGE_RATINGS_ID', null),

    /*
    |--------------------------------------------------------------------------
    | Cookie Policy
    |--------------------------------------------------------------------------
    |
    | This refers to the ID of the cookie policy page. By specifying the
    | page ID, you can easily manage and update the cookie policy link.
    | Setting it to null will disable the cookie policy feature.
    |
    */

    'cookie' => env('PAGE_COOKIE_ID', null),

    /*
    |--------------------------------------------------------------------------
    | Privacy Policy
    |--------------------------------------------------------------------------
    |
    | This refers to the ID of the privacy policy page. By specifying the
    | page ID, you can easily manage and update the privacy policy link.
    | Setting it to null will disable the privacy policy feature.
    |
    */

    'privacy' => env('PAGE_PRIVACY_ID', null),

    /*
    |--------------------------------------------------------------------------
    | Terms of Service
    |--------------------------------------------------------------------------
    |
    | This refers to the ID of the termsof service page. By specifying the
    | page ID, you can easily manage and update the terms of service link.
    | Setting it to null will disable the terms of service feature.
    |
    */

    'terms' => env('PAGE_TERMS_ID', null),

];
