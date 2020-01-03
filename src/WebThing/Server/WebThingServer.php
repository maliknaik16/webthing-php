<?php

namespace WebThing\Server;

/**
 * @file
 * Contains WebThingServer class.
 */

use WebThing\SingleThing;
use WebThing\MultipleThings;
use WebThing\ThingsInterface;

/**
 * Server to represent a Web Thing over HTTP.
 */
class WebThingServer extends Server {

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
   * Initialize the Web Thing Server.
   */
  public function __construct(ThingsInterface $things, $address = '127.0.0.1', $httpPort = 80, $wsPort = 8080, $additional_routes = null, $base_path = '') {
    parent::__construct($things, $address, $httpPort, $wsPort);

    $this->additional_routes = $additional_routes;
    $this->base_path = $base_path;

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
