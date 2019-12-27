<?php

namespace WebThing\Server;

/**
 * @file
 * High level Action base class implementation.
 */

use WebThing\Thing;

/**
 * Represents an individual action on a thing.
 */
class ThingHandler {

  /**
   * List of things managed by this server.
   *
   * @var WebThing\Thing
   */
  protected $things;

  /**
   * List of allowed host names.
   *
   * @var string
   */
  protected $hosts;

  /**
   * Initialize the handler.
   */
  public function __construct($things, $hosts) {
    $this->things = $things;
    $this->hosts = $hosts;
  }
}
