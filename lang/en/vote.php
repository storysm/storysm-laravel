<?php

return [
    'notification' => [
        'login_required' => [
            'title' => 'Login Required',
            'body' => 'Please log in to vote this story.',
            'action' => [
                'login' => 'Login',
            ],
        ],
    ],
    'resource' => [
        'model_label' => 'Vote|Votes',
        'upvote_count' => 'Like Count',
        'downvote_count' => 'Dislike Count',
    ],
    'type' => [
        'up' => 'Like',
        'down' => 'Dislike',
    ],
];
