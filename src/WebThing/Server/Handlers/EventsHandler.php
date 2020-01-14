<?php

namespace WebThing\Server\Handlers;

/**
 * @file
 * Contains EventsHandler class implementation.
 */

/**
 * Handle a request to '/events'.
 */
class EventsHandler extends BaseHandler {

  /**
   * {@inheritdoc}
   */
  public function get() {
    $thing_id = array_key_exists('thing_id', $this->getRouteArgs()) ? $this->getRouteArgs()['thing_id'] : '0';

    $thing = $this->getThing($thing_id);

    if($thing == NULL) {
      $this->sendError(404);
      return;
    }

    $this->setStatus(200);
    $this->setContentType('application/json');
    $this->write(json_encode($thing->getEventDescriptions()));
  }
}
