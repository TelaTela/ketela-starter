<?php

return [

    'page_title' => 'Masuk',

    'layout' => [
        'title' => 'Masuk ke akun anda',
        'description' => 'Isi email dan kata sandi anda di bawah untuk masuk',
    ],

    'form' => [
        'email' => [
            'label' => 'Alamat email',
            'placeholder' => 'email@example.com',
        ],

        'password' => [
            'label' => 'Kata sandi',
            'placeholder' => 'Kata sandi',
            'forgot' => 'Lupa kata sandi?',
        ],

        'remember' => [
            'label' => 'Ingat saya',
        ],

        'submit' => [
            'label' => 'Masuk',
        ],
    ],

    'no_account' => 'Belum punya akun?',
    'register' => 'Daftar',
];
