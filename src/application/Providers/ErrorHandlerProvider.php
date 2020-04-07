<?php


namespace Providers;

use Foreweather\ErrorHandler;
use Monolog\Logger;
use Phalcon\Config;
use Phalcon\Di\DiInterface;

class ErrorHandlerProvider
{
    /**
     * Registers a service provider.
     *
     * @param DiInterface $di
     *
     * @return void
     */

    public function register(DiInterface $di): void
    {
        /**
         * @var Logger $logger
         */
        $logger  = $di->getShared('logger');
        /**
         * @var Config $registry
         */
        $config  = $di->getShared('config');

        ini_set('display_errors', 'Off');
        error_reporting(E_ALL);

        $handler = new ErrorHandler($logger, $config);
        set_error_handler([$handler, 'handle']);
        register_shutdown_function([$handler, 'shutdown']);
    }
}
