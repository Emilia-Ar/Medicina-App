<?php

return [

    'vapid' => [
        // QUIÉN envía las notificaciones (un mail de contacto)
        'subject'    => env('VAPID_SUBJECT', 'mailto:' . env('MAIL_FROM_ADDRESS', 'hello@example.com')),
        'public_key' => env('VAPID_PUBLIC_KEY'),
        'private_key'=> env('VAPID_PRIVATE_KEY'),
    ],

    'database' => [
        'connection' => env('DB_CONNECTION', 'mysql'),
        'table_name' => 'push_subscriptions',
    ],

    'model' => \NotificationChannels\WebPush\PushSubscription::class,

];
