#!/usr/bin/env php
<?php
//nohup QUEUE=default php resque.php >> /path/to/your/logfile.log 2>&1 &
require('../Edge/Core/Loader.php');
use Edge\Core\Edge;
$commonConfig = include("Common/Config/config.php");
$app = new Edge($commonConfig);

$QUEUE = getenv('QUEUE');
if (empty($QUEUE)) {
    die("Set QUEUE env var containing the list of queues to work.\n");
}

$REDIS_BACKEND = getenv('REDIS_BACKEND');
$REDIS_DATABASE = getenv('REDIS_DATABASE');
$REDIS_NAMESPACE = getenv('REDIS_NAMESPACE');


$logger = $app->logger;
$queue = $app->queue;

if (!empty($REDIS_BACKEND)) {
    Resque::setBackend($REDIS_BACKEND, $REDIS_DATABASE, $REDIS_NAMESPACE);
}

$logLevel = Resque_Worker::LOG_VERBOSE;
$LOGGING = getenv('LOGGING');
$VERBOSE = getenv('VERBOSE');
$VVERBOSE = getenv('VVERBOSE');
if (!empty($LOGGING) || !empty($VERBOSE)) {
    $logLevel = Resque_Worker::LOG_NORMAL;
} elseif (!empty($VVERBOSE)) {
    $logLevel = Resque_Worker::LOG_VERBOSE;
}

$APP_INCLUDE = getenv('APP_INCLUDE');
if ($APP_INCLUDE) {
    if (!file_exists($APP_INCLUDE)) {
        die('APP_INCLUDE (' . $APP_INCLUDE . ") does not exist.\n");
    }

    require_once $APP_INCLUDE;
}

$interval = 5;
$INTERVAL = getenv('INTERVAL');
if (!empty($INTERVAL)) {
    $interval = $INTERVAL;
}

$count = 1;
$COUNT = getenv('COUNT');
if (!empty($COUNT) && $COUNT > 1) {
    $count = $COUNT;
}

if ($count > 1) {
    for ($i = 0; $i < $count; ++$i) {
        $pid = pcntl_fork();
        if ($pid == -1) {
            die("Could not fork worker " . $i . "\n");
        } elseif (!$pid) { // Child, start the worker
            startWorker($QUEUE, $logLevel, $logger, $interval);
            break;
        }
    }
} else { // Start a single worker

    $PIDFILE = getenv('PIDFILE');
    if ($PIDFILE) {
        file_put_contents($PIDFILE, getmypid()) or die('Could not write PID information to ' . $PIDFILE);
    }
    $logger->info("starting");
    startWorker($QUEUE, $logLevel, $logger, $interval);
}

function startWorker($QUEUE, $logLevel, $logger, $interval)
{
    $queues = explode(',', $QUEUE);
    $worker = new Resque_Worker($queues);
    $worker->registerLogger($logger);
    $worker->logLevel = $logLevel;
    $worker->work($interval);
}