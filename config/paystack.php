<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Paystack Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for Paystack payment gateway
    | integration. These values are used when integrating with Paystack's
    | payment APIs for processing donations.
    |
    */

    'publicKey' => env('PAYSTACK_PUBLIC_KEY'),
    'secretKey' => env('PAYSTACK_SECRET_KEY'),
    
    'paymentUrl' => env('PAYSTACK_PAYMENT_URL', 'https://api.paystack.co'),
    
    'merchantEmail' => env('PAYSTACK_MERCHANT_EMAIL'),
    
    'currency' => env('PAYSTACK_CURRENCY', 'KES'),
    
    // Supported payment channels for Kenya
    'channels' => ['card', 'mobile_money'],
    
    // Callback URLs
    'callbackUrl' => env('APP_URL') . '/donations/callback',
    'webhookUrl' => env('APP_URL') . '/paystack/webhook',
];