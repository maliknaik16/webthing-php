<?php

namespace WebThing\Server\Handlers;

/**
 * @file
 * Contains PropertiesHandler class implementation.
 */

/**
 * Handle a request to '/properties'.
 */
class PropertiesHandler extends BaseHandler {

  /**
   * {@inheritdoc}
   */
  public function get() {
    $route_args = $this->getRouteArgs();
    $thing_id = array_key_exists('thing_id', $route_args) ? $route_args['thing_id'] : '0';

    $thing = $this->getThing($thing_id);

    if($thing === NULL) {
      $this->sendError(404);
      return;
    }

    $this->setStatus(200);
    $this->setContentType('application/json');
    $this->write(json_encode($thing->getProperties()));
  }
}
