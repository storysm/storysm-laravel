<?php

return [
    'notification' => [
        'rate_limited' => [
            'title' => 'Terlalu Banyak Permintaan',
            'body' => 'Anda memberikan suara terlalu cepat. Silakan coba lagi dalam :seconds detik.',
        ],
        'login_required' => [
            'title' => 'Login Diperlukan',
            'body' => 'Anda harus masuk untuk memberikan suara.',
            'action' => [
                'login' => 'Masuk',
            ],
        ],
    ],
    'resource' => [
        'model_label' => 'Suara Komentar|Suara Komentar',
    ],
    'type' => 'Jenis Suara',
];
