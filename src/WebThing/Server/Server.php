<?php

namespace WebThing\Server;

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

use WebThing\ThingsInterface;
use WebThing\SingleThing;
use WebThing\MultipleThings;

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Matcher\UrlMatcher;

/**
 * Server to represent a Web Thing over HTTP.
 */
class Server {

  /**
   * Things managed by this server. It should be of type SingleThing
   * or MultipleThings.
   *
   * @var ThingsInterface
   */
  protected $things;

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
   * Includes all the routes associated with the Web Thing Server.
   *
   * @var \Symfony\Component\Routing\RouteCollection;
   */
  protected $routes;

  /**
   * Initialize the sockets and EventLoop.
   */
  public function __construct(ThingsInterface $things, $address = '127.0.0.1', $httpPort = 80, $wsPort = 8080) {
    $this->loop = Factory::create();
    $this->httpPort = $httpPort;
    $this->wsPort = $wsPort;
    $this->routes = new RouteCollection();

    try {
      $this->httpSocketServer = new SocketServer($address . ':' . $httpPort, $this->loop);
      $this->wsSocketServer = new SocketServer($address . ':' . $wsPort, $this->loop);

      $this->httpServer = new ReactHttpServer(function(ServerRequestInterface $request) {
        return $this->httpRequestHandler($request);
      });

      $this->wsHandler = new WebSocketHandler();
    }catch(\Exception $e) {
      echo 'Error: ' . $e->getMessage() . PHP_EOL;
    }

    $this->routes();
  }

  /**
   * HTTP Request handler callback.
   */
  public function httpRequestHandler(ServerRequestInterface $request) {

    $method = $request->getMethod() ?? 'GET';
    $this->path = $request->getUri()->getPath();

    if($method == 'GET') {
      var_dump($this->match($this->path));
      return $this->get($request);
    }else if($method == 'POST') {
      return $this->post($request);
    }else if($method == 'PUT') {
      return $this->put($request);
    }

    return $this->simpleMessage('NOT FOUND');
  }

  /**
   * The controller array.
   */
  private function controller($c_str) {
    return [
      '_controller' => $c_str,
    ];
  }

  /**
   * Defines all the routes.
   */
  public function routes() {

    if($this->things instanceof MultipleThings) {
      // The Thing routes.
      $things_handler = new Route('/{slash}', $this->controller('thingsHandler'), [
        'slash' => '\/?'
      ]);

      $thing_handler = new Route('/{thing_id}{slash}',
        $this->controller('thingHandler'), [
        'thing_id' => '\d+',
        'slash' => "\/?"
      ]);

      // The Property routes.
      $properties_handler = new Route('/{thing_id}/properties{slash}',
        $this->controller('propertiesHandler'), [
          'thing_id' => '\d+',
          'slash' => "\/?"
      ]);

      $property_handler = new Route('/{thing_id}/properties/{property_name}{slash}',
        $this->controller('propertyHandler'), [
          'thing_id' => '\d+',
          'property_name' => '[^/]+',
          'slash' => "\/?"
      ]);

      // The action routes.
      $actions_handler = new Route('/{thing_id}/actions{slash}',
        $this->controller('actionsHandler'), [
          'thing_id' => '\d+',
          'slash' => "\/?"
      ]);

      $action_handler = new Route('/{thing_id}/actions/{action_name}{slash}',
        $this->controller('actionHandler'), [
        'thing_id' => '\d+',
        'action_name' => '[^/]+',
        'slash' => "\/?"
      ]);

      $action_id_handler = new Route('/{thing_id}/actions/{action_name}/{action_id}{slash}',
        $this->controller('actionIDHandler'), [
          'thing_id' => '\d+',
          'action_name' => '[^/]+',
          'action_id' => '[^/]+',
          'slash' => "\/?"
      ]);

      // The event routes.
      $events_handler = new Route('/{thing_id}/events{slash}',
        $this->controller('eventsHandler'), [
          'thing_id' => '\d+',
          'slash' => "\/?"
      ]);

      $event_handler = new Route('/{thing_id}/events/{event_name}{slash}',
        $this->controller('eventHandler'), [
          'thing_id' => '\d+',
          'event_name' => '[^/]+',
          'slash' => "\/?"
      ]);
    }else{
      // The Thing routes.
      $thing_handler = new Route('/{slash}', $this->controller('thingHandler'), [
        'slash' => '\/?'
      ]);

      // The Property routes.
      $properties_handler = new Route('/properties{slash}',
        $this->controller('propertiesHandler'), [
          'slash' => "\/?"
      ]);

      $property_handler = new Route('/properties/{property_name}{slash}',
        $this->controller('propertyHandler'), [
          'property_name' => '[^/]+',
          'slash' => "\/?"
      ]);

      // The action routes.
      $actions_handler = new Route('/actions{slash}',
        $this->controller('actionsHandler'), [
          'slash' => "\/?"
      ]);

      $action_handler = new Route('/actions/{action_name}{slash}',
        $this->controller('actionHandler'), [
        'action_name' => '[^/]+',
        'slash' => "\/?"
      ]);

      $action_id_handler = new Route('/actions/{action_name}/{action_id}{slash}',
        $this->controller('actionIDHandler'), [
          'action_name' => '[^/]+',
          'action_id' => '[^/]+',
          'slash' => "\/?"
      ]);

      // The event routes.
      $events_handler = new Route('/events{slash}',
        $this->controller('eventsHandler'), [
          'slash' => "\/?"
      ]);

      $event_handler = new Route('/events/{event_name}{slash}',
        $this->controller('eventHandler'), [
          'event_name' => '[^/]+',
          'slash' => "\/?"
      ]);
    }

    if(isset($things_handler)) {
      $this->routes->add("things_handler", $things_handler);
    }
    $this->routes->add("thing_handler", $thing_handler);
    $this->routes->add("properties_handler", $properties_handler);
    $this->routes->add("property_handler", $property_handler);
    $this->routes->add("actions_handler", $actions_handler);
    $this->routes->add("action_handler", $action_handler);
    $this->routes->add("action_id_handler", $action_id_handler);
    $this->routes->add("events_handler", $events_handler);
    $this->routes->add("event_handler", $event_handler);
  }

  /**
   * The route match function.
   */
  public function match($path) {
    $context = new RequestContext("/");
    $matcher = new UrlMatcher($this->routes, $context);

    return $matcher->match($path);
  }

  /**
   * Start the HTTP Server.
   */
  public function startHttpServer() {
    try {
      if($this->httpServer == null) {
        // TODO: Change the message later.
        throw new \Exception('The HTTP Server is not initialized.');
      }else{
        $this->httpServer->listen($this->httpSocketServer);
      }
    }catch(\Exception $e) {
      echo 'Error: ' . $e->getMessage() . PHP_EOL;
    }
  }

  /**
   * Start the WebSocket Server
   */
  public function startWsServer() {
    try {
      if($this->wsHandler == null || $this->wsSocketServer == null) {
        // TODO: Change the message later.
        throw new \Exception('The Socket is not initialized yet.');
      }else{
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
    }catch(\Exception $e) {
      echo 'Error: ' . $e->getMessage() . PHP_EOL;
    }

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
