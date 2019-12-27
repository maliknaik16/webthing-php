<?php

namespace WebThing\Server;

/**
 * @file
 * Contains WebThingServer class.
 */

use WebThing\Thing;
use WebThing\SingleThing;
use WebThing\MultipleThings;

use React\EventLoop\Factory;
use React\Socket\Server as ServerSocket;
use React\Http\Server as HttpServer;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Server to represent a Web Thing over HTTP.
 */
class WebThingServer {

  /**
   * Things managed by this server. It should be of type SingleThing
   * or MultipleThings.
   *
   * @var Thing
   */
  protected $things;

  /**
   * Port to listen on (defaults to port 80).
   *
   * @var int
   */
  protected $port;

  /**
   * Optional host name.
   *
   * @var Thing
   */
  protected $hostname;

  /**
   * SSL options.
   *
   * @var string
   */
  protected $ssl_options;

  /**
   * List of additional routes to add to the server.
   *
   * @var string
   */
  protected $additional_routes;

  /**
   * Base URL path to use, rather than '/'.
   *
   * @var string
   */
  protected $base_path;

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
   * Initialize the Web Thing Server.
   */
  public function __construct($things, $port = 80, $hostname = null, $ssl_options = null, $additional_routes = null, $base_path = '') {
    $this->things = $things;
    $this->port = $port;
    $this->hostname = $hostname;
    $this->ssl_options = $ssl_options;
    $this->additional_routes = $additional_routes;
    $this->base_path = $base_path;

    // TODO: Add more hosts
    $system_hostname = strtolower(gethostname());

    $this->hosts = [
      'localhost',
      sprintf("localhost:%d", $this->port),
      sprintf("%s.local", $system_hostname),
      sprintf("%s.local:%d", $system_hostname, $this->port),
    ];

    $addresses = gethostbynamel(gethostname());

    foreach($addresses as $address) {
      $this->hosts[] = $address;
      $this->hosts[] = sprintf("%s:%d", $address, $this->port);
    }

    if(!is_null($this->hostname)) {
      $hname = strtolower($this->hostname);

      $this->hosts[] = $hname;
      $this->hosts[] = sprintf("%s:%d", $hname, $this->port);
    }

    $serverHandler = function(ServerRequestInterface $request) {
    };

    $this->httpServer = new HttpServer($serverHandler);

    if($this->things instanceof MultipleThings) {
      $this->multipleThings();
    }else{
      $this->singleThing();
    }

  }

  /**
   * Handle the requests for multiple things.
   */
  public function multipleThings() {
  }

  /**
   * Handle the requests for single thing.
   */
  public function singleThing() {
  }

  public function testData() {
    return $this->hosts;
  }
}
