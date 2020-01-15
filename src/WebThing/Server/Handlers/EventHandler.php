<?php

namespace WebThing\Server\Handlers;

/**
 * @file
 * Contains EventHandler class implementation.
 */

/**
 * Handle a request to '/events/<event_name>'.
 */
class EventHandler extends BaseHandler {

  /**
   * {@inheritdoc}
   */
  public function get() {
    $route_args = $this->getRouteArgs();
    $thing_id = array_key_exists('thing_id', $route_args) ? $route_args['thing_id'] : '0';
    $event_name = array_key_exists('event_name', $route_args) ? $route_args['event_name'] : NULL;

    $thing = $this->getThing($thing_id);

    if($thing == NULL) {
      $this->sendError(404);
      return;
    }

    $this->setStatus(200);
    $this->setContentType('application/json');
    $this->write(json_encode($thing->getEventDescriptions($event_name)));
  }
}
