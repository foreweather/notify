<?php

namespace Foreweather;

use Exception;
use Monolog\Logger;
use Phalcon\Cli\TaskInterface;
use Phalcon\Di\FactoryDefault\Cli as FactoryDefault;
use Phalcon\Di\ServiceProviderInterface;
use Pheanstalk\Job;
use Pheanstalk\Pheanstalk;

class Notify
{
    /**
     * @var FactoryDefault
     */
    protected $di;

    /**
     * @var bool
     */
    protected $shouldClose = false;

    /**
     * @var Pheanstalk
     */
    protected $queue;

    /**
     * @var Job
     */
    protected $job;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var TaskInterface
     */
    protected $task;

    /**
     * @var array
     */
    protected $providers = [];

    /**
     * @param array $providers
     */
    public function setup(array $providers = []): void
    {
        $this->di = new FactoryDefault();
        $this->di->set('metrics', microtime(true));

        $this->providers = $providers;

        $this->registerServices();

        $this->logger = $this->di->get('logger');

        $this->queue = $this->di->get('queue');
        $this->queue->watch('email');
        $this->queue->watch('notification');
    }

    /**
     * Registers available services
     *
     * @return void
     */
    private function registerServices()
    {
        foreach ($this->providers as $provider) {
            /**
             * @var ServiceProviderInterface $object
             */
            $object = new $provider();
            $object->register($this->di);
        }
    }

    /**
     * @param $job_request
     *
     * @return array
     * @throws Exception
     */
    protected function segments($job_request): array
    {
        $segments = explode(':', $job_request);
        if (count($segments) !== 2) {
            throw new Exception('Invalid task handle');
        }
        return $segments;
    }

    /**
     * @param array $segments
     * @param array $data
     *
     * @return bool
     * @throws Exception
     */
    protected function handle(array $segments, array $data): bool
    {
        return call_user_func_array([$this->di[$segments[0]], $segments[1]], [$data, $this->job]);
    }

    public function run(): void
    {
        $this->logger->debug('Notify is running! [' . date_create('now')->format('Y-m-d H:i:s') . ']');


        while (true) {
            $this->logger->debug('Test inline log.');
            $this->logger->error('Test logger error log.');

            try {
                $this->job = $this->queue->reserveWithTimeout(10);

                if (isset($this->job)) {
                    $data = json_decode($this->job->getData(), true);

                    if (!$segments = $this->segments($data['job'])) {
                        continue;
                    }

                    if ($this->handle($segments, $data['payload'])) {
                        $this->logger->debug("Task completed!");
                        $this->queue->delete($this->job);
                        $this->shouldClose();
                    } else {
                        $this->logger->error("Task buried because could not success.");
                        $this->queue->bury($this->job);
                        continue;
                    }
                }
            } catch (Exception $e) {
                $this->logger->error("Task failed and buried: " . $e->getMessage());
                $this->queue->bury($this->job);
                continue;
            }
        }
    }

    public function shouldClose(): void
    {
        if ($this->shouldClose) {
            die('closed!');
        }
    }
}
