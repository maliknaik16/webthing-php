<?php

namespace WebThing\Server;
error_reporting(E_ALL);

/**
 * @file
 * Contains Server class.
 */

use React\EventLoop\Factory;
use React\Socket\Server as SocketServer;
use React\Http\Server as ReactHttpServer;
use React\Http\Response;

use Psr\Http\Message\ServerRequestInterface;

use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\Http\HttpServer;

/**
 * Server to represent a Web Thing over HTTP.
 */
class Server {

  /**
   * The Socket Server.
   *
   * @var \React\Socket\Server
   */
  protected $httpSocketServer;

  /**
   * The WebSocket Server.
   *
   * @var \React\Socket\Server
   */
  protected $webSocketServer;

  /**
   * The HTTP port.
   *
   * @var int
   */
  protected $httpPort;

  /**
   * The Web Socket port.
   *
   * @var int
   */
  protected $wsPort;

  /**
   * The EventLoop.
   *
   * @var \React\EventLoop\Factory
   */
  protected $loop;

  /**
   * The Host name.
   *
   * @var string
   */
  protected $hostname;

  /**
   * Array of hosts.
   *
   * @var array
   */
  protected $hosts;

  /**
   * HTTP Server.
   *
   * @var React\Http\Server
   */
  protected $httpServer;

  /**
   * The WebSocket handler object.
   *
   * @var \Ratchet\MessageComponentInterface
   */
  protected $wsHandler;

  /**
   * The requested path.
   */
  protected $path;

  /**
   * Initialize the sockets and EventLoop.
   */
  public function __construct($address = '127.0.0.1', $httpPort = 80, $wsPort = 8080) {
    $this->loop = Factory::create();
    $this->httpPort = $httpPort;
    $this->wsPort = $wsPort;

    $this->httpSocketServer = new SocketServer($address . ':' . $httpPort, $this->loop);
    $this->wsSocketServer = new SocketServer($address . ':' . $wsPort, $this->loop);

    $this->httpServer = new ReactHttpServer(function(ServerRequestInterface $request) {
      return $this->httpRequestHandler($request);
    });

    $this->wsHandler = new WebSocketHandler();
  }

  /**
   * HTTP Request handler callback.
   */
  public function httpRequestHandler(ServerRequestInterface $request) {

    $method = $request->getMethod() ?? 'GET';
    $this->path = $request->getUri()->getPath();

    if($method == 'GET') {
      return $this->get($request);
    }else if($method == 'POST') {
      return $this->post($request);
    }else if($method == 'PUT') {
      return $this->put($request);
    }

    return $this->simpleMessage('NOT FOUND');
  }

  /**
   * Start the HTTP Server.
   */
  public function startHttpServer() {
    $this->httpServer->listen($this->httpSocketServer);
  }

  /**
   * Start the WebSocket Server
   */
  public function startWsServer() {
    $webSocket = new IoServer(
      new HttpServer(
        new WsServer(
          $this->wsHandler
        )
      ),
      $this->wsSocketServer,
      $this->loop
    );
  }

  /**
   * Start the HTTP Server.
   */
  public function get(ServerRequestInterface $request) {
    return $this->simpleMessage('GET Request');
  }

  /**
   * Start the HTTP Server.
   */
  public function post(ServerRequestInterface $request) {
    return $this->simpleMessage('POST Request');
  }

  /**
   * Start the HTTP Server.
   */
  public function put(ServerRequestInterface $request) {
    return $this->simpleMessage('PUT Request');
  }

  /**
   * Simple Message Response.
   */
  public function simpleMessage($msg) {
    return new Response(200, [
        'Content-Type' => 'text/plain',
      ],
      $msg
    );
  }

  /**
   * Start the loop.
   */
  public function startLoop() {
    $this->loop->run();
  }

  /**
   * Start the server.
   */
  public function startServer() {
    $this->startHttpServer();
    $this->startWsServer();
    $this->loop->run();
  }
}
