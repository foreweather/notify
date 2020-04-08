<?php


namespace Providers;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogHandler;
use Monolog\Handler\SyslogUdpHandler;
use Monolog\Logger;
use Phalcon\Di\DiInterface;
use Phalcon\Di\ServiceProviderInterface;

class LoggerProvider implements ServiceProviderInterface
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
            'logger',
            function () {
                /**
                 * the default date format is "Y-m-d H:i:s"
                 */
                $dateFormat = "Y-m-d H:i:s";
                /**
                 * the default output format is "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n"
                 */
                $output = "%datetime% %message%\n";
                /**
                 * create a formatter
                 */
                $formatter = new LineFormatter($output, $dateFormat);
                $sys_log   = new SyslogHandler('notify-logs');
                $sys_log->setFormatter($formatter);

                /**
                 * Create a handler
                 */
                $handler = new SyslogUdpHandler(
                    'syslog',
                    5514,
                    LOG_USER,
                    Logger::DEBUG
                );
                $handler->setFormatter($formatter);

                $file           = 'notify';
                $path           = './var/logs';
                $logFile        = $path . '/' . $file . '.log';
                $formatter      = new LineFormatter("[%datetime%][%level_name%] %message%\n");
                $stream_handler = new StreamHandler($logFile, Logger::DEBUG);
                $stream_handler->setFormatter($formatter);

                /**
                 * bind it to a logger object
                 */
                $logger = new Logger('notify-logger');
                $logger->pushHandler($sys_log);
                $logger->pushHandler($stream_handler);
                $logger->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));

                return $logger;
            }
        );
    }
}
