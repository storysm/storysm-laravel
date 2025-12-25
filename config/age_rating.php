<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Age Rating Limit Years
    |--------------------------------------------------------------------------
    |
    | This value determines the maximum age rating (in years) that a user
    | can view. Stories with an effective age rating higher than this
    | value will be filtered out.
    |
    | If not set in the .env file, the default value of 16 will be used.
    |
    */

    'limit_years' => env('AGE_RATING_LIMIT_YEARS', 16),

    /*
    |--------------------------------------------------------------------------
    | Cookie Duration Minutes
    |--------------------------------------------------------------------------
    |
    | This value determines the number of minutes that the 'user_age' cookie
    | should be valid for. By default, this is set to 30 days. Override this
    | via the AGE_RATING_COOKIE_DURATION environment variable.
    |
    */

    'cookie_duration_minutes' => env('AGE_RATING_COOKIE_DURATION', 60 * 24 * 30),
];
