<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Age Rating Guest Limit Years
    |--------------------------------------------------------------------------
    |
    | This value determines the maximum age rating (in years) that a guest
    | user can view. Stories with an effective age rating higher than this
    | value will be filtered out for unauthenticated users.
    |
    | If not set in the .env file, the default value of 16 will be used.
    |
    */

    'guest_limit_years' => env('AGE_RATING_GUEST_LIMIT_YEARS', 16),
];
