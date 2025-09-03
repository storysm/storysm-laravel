<?php

return [
    'form' => [
        'actions' => [
            'submit' => 'Kirim',
            'login' => 'Masuk',
        ],
        'body' => [
            'label' => 'Komentar',
            'placeholder' => 'Tambahkan komentar…',
        ],
        'notification' => [
            'created' => 'Komentar berhasil dibuat.',
        ],
        'section' => [
            'heading' => [
                'write' => 'Tulis Komentar',
                'login_required' => 'Login Diperlukan untuk Menulis Komentar',
            ],
            'description' => [
                'login_required' => 'Silakan masuk untuk menambahkan komentar.',
            ],
        ],
        'validation' => [
            'body_required' => 'Isi komentar wajib diisi.',
        ],
    ],
    'resource' => [
        'comment_count' => 'Jumlah Komentar',
        'model_label' => 'Komentar|Komentar',
        'parent_comment' => 'Komentar Induk',
        'replied_comment' => 'Komentar Dibalas',
        'reply_count' => 'Jumlah Balasan',
        'reply_label' => 'Balasan|Balasan',
        'upvote_count' => 'Jumlah Upvote',
        'downvote_count' => 'Jumlah Downvote',
        'vote_count' => 'Total Suara',
        'vote_score' => 'Skor Suara',
    ],
];
