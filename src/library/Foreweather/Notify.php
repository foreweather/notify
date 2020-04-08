<?php

namespace Foreweather;

use Exception;
use League\OAuth2\Client\Provider\GenericProvider;
use Phalcon\Cli\TaskInterface;
use Phalcon\Di\FactoryDefault\Cli as FactoryDefault;
use Phalcon\Di\ServiceProviderInterface;
use Phalcon\Logger;
use Pheanstalk\Job;
use Pheanstalk\Pheanstalk;
use Psr\Log\LoggerInterface;

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
     * @var LoggerInterface
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
     * @param     $message
     * @param int $level
     */
    private function log($message, int $level = Logger::INFO)
    {
        $loggy      = [
            Logger::CRITICAL => 'CRITICAL',
            Logger::ALERT    => 'ALERT',
            Logger::ERROR    => 'ERROR',
            Logger::WARNING  => 'WARNING',
            Logger::NOTICE   => 'NOTICE',
            Logger::INFO     => 'INFO',
            Logger::DEBUG    => 'DEBUG',
            Logger::CUSTOM   => 'CUSTOM',
        ];
        $timeString = date('Y-m-d H:i:s');
        $message    = "[{$loggy[$level]}] [{$timeString}] {$message}";
        if (!empty($this->logger)) {
            $this->logger->log(1, $message);
        }

        echo $message . PHP_EOL;
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

    /**
     * @param string $message
     */
    public function console(string $message)
    {
        echo $message . PHP_EOL;
    }

    public function run(): void
    {
        $this->console('Notify is running! ['. date_create('Y-m-d H:i:s').']');
        /**
         * @var \Monolog\Logger $logger
         */
        $logger = $this->di->get('logger');

        while (true) {
            $logger->error('Test');

            try {
                $this->job = $this->queue->reserveWithTimeout(10);

                if (isset($this->job)) {
                    $data = json_decode($this->job->getData(), true);

                    if (!$segments = $this->segments($data['job'])) {
                        continue;
                    }

                    if ($this->handle($segments, $data['payload'])) {
                        $this->log("Task completed!");
                        $this->queue->delete($this->job);
                        $this->shouldClose();
                    } else {
                        $this->log("Task buried");
                        $this->queue->bury($this->job);
                        continue;
                    }
                }
            } catch (Exception $e) {
                $this->log("Task failed and buried: " . $e->getMessage());
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
