<?php
declare(strict_types=1);

defined('APPLICATION_ENV') || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ?
    getenv('APPLICATION_ENV') : 'development'));

use Foreweather\Notify;

require_once 'autoload.php';

$worker = new Notify();
$worker->setup(include_once 'application/config/providers.php');
$worker->run();
