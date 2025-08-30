<?php

return [
    'form' => [
        'actions' => [
            'submit' => 'Submit',
            'login' => 'Login',
        ],
        'body' => [
            'label' => 'Comment',
            'placeholder' => 'Add a comment…',
        ],
        'notification' => [
            'created' => 'Comment created successfully.',
        ],
        'section' => [
            'heading' => [
                'write' => 'Write Comment',
                'login_required' => 'Login Required to Write Comment',
            ],
            'description' => [
                'login_required' => 'Please log in to add a comment.',
            ],
        ],
        'validation' => [
            'body_required' => 'Comment body is required.',
        ],
    ],
    'resource' => [
        'comment_count' => 'Comment Count',
        'model_label' => 'Comment|Comments',
        'parent_comment' => 'Parent Comment',
        'replied_comment' => 'Replied Comment',
        'reply_count' => 'Reply Count',
        'reply_label' => 'Reply|Replies',
        'upvote_count' => 'Upvotes',
        'downvote_count' => 'Downvotes',
        'vote_count' => 'Total Votes',
        'vote_score' => 'Vote Score',
    ],
];
