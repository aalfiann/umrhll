<?php

// Initialize Slim App
$app = new \Slim\App(["settings" => $config]);

// Create container
$container = $app->getContainer();
$settings = $container->get('settings');
$app->add(new \Slim\HttpCache\Cache('public',604800));

// Register component Http-cache
$container['cache'] = function () {
    return new \Slim\HttpCache\CacheProvider();
};

// Get visitor ip address
$container['visitorip'] = function(){
    $client  = @$_SERVER['HTTP_CLIENT_IP'];
    $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
	$remote  = $_SERVER['REMOTE_ADDR'];
	if(filter_var($client, FILTER_VALIDATE_IP)) {
        $ip = $client;
	} elseif(filter_var($forward, FILTER_VALIDATE_IP)) {
	    $ip = $forward;
	} else {
        $ip = $remote;
	}
	return $ip;
};

// Default generate eTag per 5minutes
$container['etag'] = function(){
    $fix = date('Y-m-d H:');
    $rate = date('i');
    $maxminute = 60;
    $intervalminute = 5;

    $n=0;
    for ($i = 0; $i <= $maxminute; $i+=$intervalminute) {
        if($i<=$rate) {$n++;}
    }
    return md5($fix.$n);
};

// Default generate eTag per 30minutes
$container['etag30min'] = function(){
    $fix = date('Y-m-d H:');
    $rate = date('i');
    $maxminute = 60;
    $intervalminute = 30;

    $n=0;
    for ($i = 0; $i <= $maxminute; $i+=$intervalminute) {
        if($i<=$rate) {$n++;}
    }
    return md5($fix.$n);
};

// Default generate eTag per every hour
$container['etag1hour'] = function(){
    $fix = date('Y-m-d ');
    $rate = date('H');
    $maxhour = 24;
    $intervalhour = 1;

    $n=0;
    for ($i = 0; $i <= $maxhour; $i+=$intervalhour) {
        if($i<=$rate) {$n++;}
    }
    return md5($fix.$n);
};

// Default generate eTag per 2hours
$container['etag2hour'] = function(){
    $fix = date('Y-m-d ');
    $rate = date('H');
    $maxhour = 24;
    $intervalhour = 2;

    $n=0;
    for ($i = 0; $i <= $maxhour; $i+=$intervalhour) {
        if($i<=$rate) {$n++;}
    }
    return md5($fix.$n);
};

// Register component Monolog
$container['logger'] = function($c) {
    $logger = new \Monolog\Logger('reSlim_logger');
    $file_handler = new \Monolog\Handler\StreamHandler("../logs/app.log");
    $formatter = new \Monolog\Formatter\LineFormatter(null, null, false, true);
    $file_handler->setFormatter($formatter);
    $logger->pushHandler($file_handler);
    return $logger;
};

// Register component master database connection on container
$container['db'] = function ($c) {
    if (isset($c['settings']['db'])) {
        $db = $c['settings']['db'];
        // Simmple Load Balancer
        if(is_array($db['host'])){
            $numserver = mt_rand(0,(count($db['host'])-1));
            $dbhost = $db['host'][$numserver];
            $dbname = $db['dbname'][$numserver];
            $dbuser = $db['user'][$numserver];
            $dbpass = $db['pass'][$numserver];
            $pdo = new \classes\LazyPDO("mysql:host=" . $dbhost . ";dbname=" . $dbname, $dbuser, $dbpass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } else {
            $pdo = new \classes\LazyPDO("mysql:host=" . $db['host'] . ";dbname=" . $db['dbname'], $db['user'], $db['pass']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        }
        return $pdo;
    }
    return null;
};

// Register component slave database connection on container
$container['dbslave'] = function ($c) {
    if (isset($c['settings']['dbslave'])) {
        $db = $c['settings']['dbslave'];
        // Simmple Load Balancer
        if(is_array($db['host'])){
            $numserver = mt_rand(0,(count($db['host'])-1));
            $dbhost = $db['host'][$numserver];
            $dbname = $db['dbname'][$numserver];
            $dbuser = $db['user'][$numserver];
            $dbpass = $db['pass'][$numserver];
            $pdo = new \classes\LazyPDO("mysql:host=" . $dbhost . ";dbname=" . $dbname, $dbuser, $dbpass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } else {
            $pdo = new \classes\LazyPDO("mysql:host=" . $db['host'] . ";dbname=" . $db['dbname'], $db['user'], $db['pass']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        }
        return $pdo;
    }
    return null;
};

// Register component phpmailer on container
$container['mail'] = function ($c) { return new PHPMailer; };

// Override the default Not Found Handler
$container['notFoundHandler'] = function ($container) {
    return function ($request, $response) use ($container) {
        $data = [
            'status' => 'error',
            'code' => '404',
            'message' => $response->withStatus(404)->getReasonPhrase()
        ];
        return $container->get('response')
            ->withStatus(404)
            ->withHeader('Content-type', 'application/json;charset=utf-8')
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->write(json_encode($data, JSON_PRETTY_PRINT));
    };
};

// Override the default Not Allowed Handler
$container['notAllowedHandler'] = function ($container) {
    return function ($request, $response, $methods) use ($container) {
        $data = [
            'status' => 'error',
            'code' => '405',
            'message' => $response->withStatus(405)->getReasonPhrase().', method must be one of: ' . implode(', ', $methods)
        ];
        return $container['response']
            ->withStatus(405)
            ->withHeader('Allow', implode(', ', $methods))
            ->withHeader('Content-type', 'application/json;charset=utf-8')
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->write(json_encode($data, JSON_PRETTY_PRINT));
    };
};

// Override the slim error handler
$container['errorHandler'] = function ($container) {
    return function ($request, $response, $exception) use ($container) {
        $container->logger->addInfo('{ 
"code": '.json_encode($exception->getCode()).', 
"message": '.json_encode($exception->getMessage()).'}',['file'=>$exception->getFile(),'line'=>$exception->getLine()]);
        $response->getBody()->rewind();
        $data = [
            'status' => 'error',
            'code' => $exception->getCode(),
            'message' => trim($exception->getMessage()),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => explode("\n", $exception->getTraceAsString())
        ];
        return $response
            ->withStatus(500)
            ->withHeader('Content-type', 'application/json;charset=utf-8')
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->write(json_encode($data, JSON_PRETTY_PRINT));
    };
};

// Override PHP 7 error handler
$container['phpErrorHandler'] = function ($container) {
    return $container['errorHandler'];
};

//PHP 5 Error Handler
set_error_handler(function ($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return;
    }
    throw new \ErrorException($message, 0, $severity, $file, $line);
});