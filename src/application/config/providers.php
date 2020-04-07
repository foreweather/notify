<?php

use Providers\ConfigProvider;
use Providers\NotifyProvider;
use Providers\OAuthClientProvider;
use Providers\QueueProvider;

/**
 * Provider class names
 */
return [
    ConfigProvider::class,
    OAuthClientProvider::class,
    QueueProvider::class,
    NotifyProvider::class
];
