<?php

return [

    'page_title' => 'Log In',

    'layout' => [
        'title' => 'Log in to your account',
        'description' => 'Enter your email and password below to log in',
    ],

    'form' => [
        'email' => [
            'label' => 'Email address',
            'placeholder' => 'email@example.com',
        ],

        'password' => [
            'label' => 'Password',
            'placeholder' => 'Password',
            'forgot' => 'Forgot your password?',
        ],

        'remember' => [
            'label' => 'Remember me',
        ],

        'submit' => [
            'label' => 'Log in',
        ],
    ],

    'no_account' => "Don't have an account?",
    'register' => 'Sign up',
];
