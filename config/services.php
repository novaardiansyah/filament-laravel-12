<?php

return [

  /*
  |--------------------------------------------------------------------------
  | Third Party Services
  |--------------------------------------------------------------------------
  |
  | This file is for storing the credentials for third party services such
  | as Mailgun, Postmark, AWS and more. This file provides the de facto
  | location for this type of information, allowing packages to have
  | a conventional file to locate the various service credentials.
  |
  */

  'postmark' => [
    'token' => env('POSTMARK_TOKEN'),
  ],

  'resend' => [
    'key' => env('RESEND_KEY'),
  ],

  'ses' => [
    'key' => env('AWS_ACCESS_KEY_ID'),
    'secret' => env('AWS_SECRET_ACCESS_KEY'),
    'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
  ],

  'slack' => [
    'notifications' => [
      'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
      'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
    ],
  ],

  'cloudflare' => [
    'turnstile' => [
      'site_url'   => 'https://challenges.cloudflare.com/turnstile/v0/siteverify',
      'secret_key' => env('CF_CAPTCHA_SECRET', '-'),
    ],
  ],

  'ipinfo' => [
    'token' => env('IPINFO_TOKEN', ''),
  ],

  'tinyurl' => [
    'token'        => env('TINYURL_API_KEY', ''),
    'url'          => env('TINYURL_URL', ''),
    'alias_domain' => env('TINYURL_DOMAIN_ALIAS', ''),
  ]
];
