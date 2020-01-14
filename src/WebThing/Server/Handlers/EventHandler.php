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
    $thing_id = isset($this->getRouteArgs()['thing_id']) ?: '0';
    $event_name = isset($this->getRouteArgs()['event_name']) ?: NULL;

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
