<?php


namespace Providers;

use Phalcon\Di\DiInterface;
use Phalcon\Di\ServiceProviderInterface;
use Pheanstalk\Pheanstalk;

class QueueProvider  implements ServiceProviderInterface
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
        $config = $di->getShared('config')->get('queue')->toArray();

        $di->setShared(
            'queue',
            function () use ($config) {
                echo 'Pheanstalk instance created' . PHP_EOL;

                return Pheanstalk::create($config['host']);
            }
        );
    }
}
