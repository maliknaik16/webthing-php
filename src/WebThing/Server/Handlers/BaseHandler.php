<?php

namespace WebThing\Server\Handlers;

/**
 * @file
 * Contains BaseHandler class implementation.
 */

use Crimson\RequestHandler;

/**
 * The Base handler that is initialized with a thing.
 */
class BaseHandler extends RequestHandler {

  /**
   * List of things managed by this server.
   *
   * @var WebThing\Thing
   */
  protected $things;

  /**
   * List of allowed host names.
   *
   * @var array
   */
  protected $hosts;

  /**
   * Initialize the handler.
   */
  public function initialize() {
    $class_args = $this->getClassArgs();
    $this->things = $class_args['things'];
    $this->hosts = $class_args['hosts'];
  }

  /**
   * Validate Host header.
   */
  public function prepare() {
    $host = $this->getRequest()->getHeaders()['Host'];
    if(!is_string($host)) {
      $host = $host[0];
    }

    if($host !== NULL && in_array(strtolower($host), $this->hosts)) {
      return;
    }

    $this->sendError(403);
  }

  /**
   * Get the thing.
   *
   * @param string $thing_id
   *  ID of the thing to get.
   *
   * @return mixed
   */
  protected function getThing($thing_id) {
    return $this->things->getThing($thing_id);
  }

  /**
   * Set the default headers for all requests.
   */
  public function setDefaultHeaders() {
    $this->setHeader('Access-Control-Allow-Origin', '*');
    $this->setHeader('Access-Control-Allow-Headers',
                     'Origin, X-Requested-With, Content-Type, Accept');
    $this->setHeader('Access-Control-Allow-Methods',
                     'GET, HEAD, PUT, POST, DELETE');
  }
}
