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
            function () use ($di) {

                $notify = new NotifyTask();
                $notify->setLogger($di->getShared('logger'));
                return $notify;
            }
        );
    }
}
