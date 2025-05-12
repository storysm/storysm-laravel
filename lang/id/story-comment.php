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
        'replied_comment' => 'Komentar Dibalas',
        'reply_count' => 'Jumlah Balasan',
        'reply_label' => 'Balasan|Balasan',
    ],
];
