<?php


namespace Providers;

use Phalcon\Di\DiInterface;
use Phalcon\Di\ServiceProviderInterface;
use Tasks\Notify\NotifyTask;

class NotifyProvider implements ServiceProviderInterface
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
        $di->setShared(
            'notify',
            function () {
                //echo 'NotifyTask instance created' . PHP_EOL;
                return new NotifyTask();
            }
        );
    }
}
