<?php

namespace WebThing\Server\Handlers;

/**
 * @file
 * PropertiesHandler class implementation.
 */

/**
 * Handle a request to '/properties'.
 */
class PropertiesHandler extends BaseHandler {

  /**
   * {@inheritdoc}
   */
  public function get() {
    $thing_id = isset($this->getRouteArgs()['thing_id']) ?: '0';
    $thing = $this->getThing($thing_id);

    if($thing == NULL) {
      $this->sendError(404);
      return;
    }

    $this->setContentType('application/json');
    $this->write(json_decode($thing->getProperites()));
  }
}
