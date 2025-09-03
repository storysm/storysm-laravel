<?php

return [
    'notification' => [
        'rate_limited' => [
            'title' => 'Too Many Requests',
            'body' => 'You are voting too quickly. Please try again in :seconds seconds.',
        ],
        'login_required' => [
            'title' => 'Login Required',
            'body' => 'You must be logged in to vote.',
            'action' => [
                'login' => 'Login',
            ],
        ],
    ],
    'resource' => [
        'model_label' => 'Comment Vote|Comment Votes',
    ],
    'type' => 'Vote Type',
];
