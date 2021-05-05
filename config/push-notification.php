<?php

return array(

    'IOSUser'     => array(
        'environment' => env('IOS_USER_ENV', 'production'),
        'certificate' => app_path().'/apns/user/Tranxit_enterprise_user.pem',
        'passPhrase'  => env('IOS_USER_PUSH_PASS', 'Appoets123$'),
        'service'     => 'apns'
    ),
    'IOSProvider' => array(
        'environment' => env('IOS_PROVIDER_ENV', 'production'),
        'certificate' => app_path().'/apns/provider/Tranxit_enterprise_pro.pem',
        'passPhrase'  => env('IOS_PROVIDER_PUSH_PASS', 'Appoets123$'),
        'service'     => 'apns'
    ),
    'IOSProviderVoip' => array(
        'environment' => env('IOS_PROVIDER_ENV', 'production'),
        'certificate' => app_path().'/apns/provider/KalVoip.pem',
        'passPhrase'  => env('IOS_PROVIDER_PUSH_PASS', 'apple'),
        'service'     => 'apns'
    ),
    'AndroidUser' => array(
        'environment' => env('ANDROID_USER_ENV', 'production'),
        'apiKey'      => env('ANDROID_USER_PUSH_KEY', 'AIzaSyAEWvI5rlAmkXmCb2x3e0XzCIqM93I8Khs'),
        'service'     => 'gcm'
    ),
    'AndroidProvider' => array(
        'environment' => env('ANDROID_PROVIDER_ENV', 'production'),
        'apiKey'      => env('ANDROID_PROVIDER_PUSH_KEY', 'AIzaSyAEWvI5rlAmkXmCb2x3e0XzCIqM93I8Khs'),
        'service'     => 'gcm'
    )

);