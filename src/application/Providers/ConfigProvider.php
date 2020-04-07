<?php


namespace Providers;

use Phalcon\Di\DiInterface;
use Phalcon\Di\ServiceProviderInterface;
use Phalcon\Config;

class ConfigProvider implements ServiceProviderInterface
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

        $di->setShared('config', function () {

            echo 'Config instance created' . PHP_EOL;

            $config = include 'application/config/config.php';

            return new Config($config);
        });
    }
}
