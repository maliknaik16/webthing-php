<?php

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use WebThing\TestServer;

require dirname(__DIR__) . '../../vendor/autoload.php';

//$server = IoServer::factory(new HttpServer(new WsServer(new TestServer())), 8080);

$server = new \Ratchet\App('localhost', 8081);
$server->route('/test', new TestServer, array('*'));

$server->run();
