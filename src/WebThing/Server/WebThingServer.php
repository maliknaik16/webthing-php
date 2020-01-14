<?php

namespace WebThing\Server;

/**
 * @file
 * Contains WebThingServer class.
 */

use WebThing\SingleThing;
use WebThing\MultipleThings;
use WebThing\ThingsInterface;
use WebThing\Server\Handlers\WebSocketThingHandler;

use React\EventLoop\Factory;
use React\Socket\Server;

use Ratchet\App as Application;

use Symfony\Component\Routing\Route;

use Crimson\App;
use Crimson\HttpServer;

/**
 * Server to represent a Web Thing over HTTP.
 */
class WebThingServer {


  /**
   * Things managed by this server. It should be of type SingleThing
   * or MultipleThings.
   *
   * @var WebThing\ThingsInterface
   */
  protected $things;

  /**
   * The Name of the thing.
   *
   * @var string
   */
  protected $name;

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
   * The hostname.
   *
   * @var string
   */
  protected $hostname;

  /**
   * List of additional routes to add to the server.
   *
   * @var array|null
   */
  protected $additional_routes;

  /**
   * Base URL path to use, rather than '/'.
   *
   * @var string
   */
  protected $base_path;

  /**
   * An array of handlers.
   *
   * @var array
   */
  protected $handlers;

  /**
   * HTTP Server.
   *
   * @var Crimson\HttpServer.
   */
  protected $server;

  /**
   * The Ratchet App.
   *
   * @var \Ratchet\App.
   */
  protected $webSocketApp;

  /**
   * Initialize the Web Thing Server.
   */
  public function __construct($things, $hostname = '127.0.0.1', $httpPort = 80, $wsPort = 8080, $tls_options = [], $additional_routes = NULL, $base_path = '', $loop = NULL) {
    $this->things = $things;
    $this->name = $things->getName();
    $this->hostname = $hostname;
    $this->httpPort = $httpPort;
    $this->wsPort = $wsPort;
    $this->additional_routes = $additional_routes;
    $this->base_path = rtrim($base_path, '/');

    // TODO: Add more hosts
    $system_hostname = strtolower(gethostname());

    $this->hosts = [
      'localhost',
      sprintf("localhost:%d", $this->httpPort),
      sprintf("localhost:%d", $this->wsPort),
      sprintf("%s.local", $system_hostname),
      sprintf("%s.local:%d", $system_hostname, $this->httpPort),
      sprintf("%s.local:%d", $system_hostname, $this->wsPort),
    ];

    $addresses = gethostbynamel(gethostname());

    foreach($addresses as $address) {
      $this->hosts[] = $address;
      $this->hosts[] = sprintf("%s:%d", $address, $this->httpPort);
      $this->hosts[] = sprintf("%s:%d", $address, $this->wsPort);
    }

    if(!is_null($this->hostname)) {
      $hname = strtolower($this->hostname);

      $this->hosts[] = $hname;
      $this->hosts[] = sprintf("%s:%d", $hname, $this->httpPort);
      $this->hosts[] = sprintf("%s:%d", $hname, $this->wsPort);
    }

    if($this->things instanceof MultipleThings) {
      foreach(array_values($this->things->getThings()) as $i => $thing) {
        $thing->setHrefPrefix(sprintf("%s/%s", $this->base_path, $i));
      }
      $this->multipleThings();
    } elseif($this->things instanceof SingleThing) {
      $this->things->getThing()->setHrefPrefix($this->base_path);
      $this->singleThing();
    }

    $app = new App($this->handlers);

    if(!empty($additional_routes)) {
      $app->addHandlers($additional_routes);
    }

    if($loop == NULL) {
      $eventLoop = Factory::create();
    }else{
      $eventLoop = $loop;
    }
    $this->server = new HttpServer($app, $tls_options, $hostname, $httpPort, $eventLoop);
    $this->webSocketApp = new Application('localhost', $wsPort, $hostname, $eventLoop);
    $thingHandler = new WebSocketThingHandler($this->things, $this->hosts, $eventLoop);
    $this->webSocketApp->route('/', $thingHandler, array('*'));
    $this->webSocketApp->route('/{thing_id}', $thingHandler, array('*'));
  }

  /**
   * Handle the requests for multiple things.
   */
  public function multipleThings() {
    $class_args = [
      'things' => $this->things,
      'hosts' => $this->hosts
    ];

    $class_args_ws = [
      'things' => $this->things,
      'hosts' => $this->hosts,
      'ws_port' => $this->wsPort,
    ];

    $this->handlers = [
      [
        '\/?',
        'WebThing\Server\Handlers\ThingsHandler',
        $class_args_ws,
      ],
      [
        '\/(?P<thing_id>\d+)\/?',
        'WebThing\Server\Handlers\ThingHandler',
        $class_args_ws,
      ],
      [
        '\/(?P<thing_id>\d+)\/properties\/?',
        'WebThing\Server\Handlers\PropertiesHandler',
        $class_args,
      ],
      [
        '\/(?P<thing_id>\d+)\/properties\/' .
        '(?P<property_name>[^\/]+)\/?',
        'WebThing\Server\Handlers\PropertyHandler',
        $class_args,
      ],
      [
        '\/(?P<thing_id>\d+)\/actions\/?',
        'WebThing\Server\Handlers\ActionsHandler',
        $class_args,
      ],
      [
        '\/(?P<thing_id>\d+)\/actions\/(?P<action_name>[^\/]+)\/?',
        'WebThing\Server\Handlers\ActionHandler',
        $class_args,
      ],
      [
        '\/(?P<thing_id>\d+)\/actions\/' .
        '(?P<action_name>[^\/]+)\/(?P<action_id>[^\/]+)\/?',
        'WebThing\Server\Handlers\ActionIDHandler',
        $class_args,
      ],
      [
        '\/(?P<thing_id>\d+)\/events\/?',
        'WebThing\Server\Handlers\EventsHandler',
        $class_args,
      ],
      [
        '\/(?P<thing_id>\d+)\/events\/(?P<event_name>[^\/]+)\/?',
        'WebThing\Server\Handlers\EventHandler',
        $class_args,
      ],
    ];
  }

  /**
   * Handle the requests for single thing.
   */
  public function singleThing() {

    $class_args = [
      'things' => $this->things,
      'hosts' => $this->hosts
    ];

    $class_args_ws = [
      'things' => $this->things,
      'hosts' => $this->hosts,
      'ws_port' => $this->wsPort,
    ];

    $this->handlers = [
      [
        '\/?',
        'WebThing\Server\Handlers\ThingHandler',
        $class_args_ws,
      ],
      [
        '\/properties\/?',
        'WebThing\Server\Handlers\PropertiesHandler',
        $class_args,
      ],
      [
        '\/properties\/' .
        '(?P<property_name>[^\/]+)\/?',
        'WebThing\Server\Handlers\PropertyHandler',
        $class_args,
      ],
      [
        '\/actions\/?',
        'WebThing\Server\Handlers\ActionsHandler',
        $class_args,
      ],
      [
        '\/actions\/(?P<action_name>[^\/]+)\/?',
        'WebThing\Server\Handlers\ActionHandler',
        $class_args,
      ],
      [
        '\/actions\/' .
        '(?P<action_name>[^\/]+)\/(?P<action_id>[^\/]+)\/?',
        'WebThing\Server\Handlers\ActionIDHandler',
        $class_args,
      ],
      [
        '\/events\/?',
        'WebThing\Server\Handlers\EventsHandler',
        $class_args,
      ],
      [
        '\/events\/(?P<event_name>[^\/]+)\/?',
        'WebThing\Server\Handlers\EventHandler',
        $class_args,
      ],
    ];
  }

  public function start() {
    $this->server->start();
  }

  public function startWebSocket() {
    $this->webSocketApp->run();
  }

  public function stop() {
    $this->server->stop();
  }
}
