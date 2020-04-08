<?php

use Providers\ConfigProvider;
use Providers\ErrorHandlerProvider;
use Providers\LoggerProvider;
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
    NotifyProvider::class,
    LoggerProvider::class,
    ErrorHandlerProvider::class
];
