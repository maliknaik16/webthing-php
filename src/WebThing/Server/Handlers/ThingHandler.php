<?php

namespace WebThing\Server\Handlers;

/**
 * @file
 * Contains ThingHandler class implementation.
 */

/**
 * Handle a request to '/' when the server manages multiple things.
 */
class ThingHandler extends BaseHandler {

  /**
   * Handle a GET request.
   */
  public function get() {
    $thing_id = array_key_exists('thing_id', $this->getRouteArgs()) ? $this->getRouteArgs()['thing_id'] : '0';
    $thing = $this->getThing($thing_id);

    if($thing == NULL) {
      $this->sendError(404);
      return;
    }

    $description = $thing->asThingDescription();
    $scheme = $this->getRequest()->getUri()->getScheme();

    array_push($description['links'], [
      'rel' => 'alternate',
      'href' => sprintf("%s%s", $scheme, $thing->getHref()),
    ]);

    $host = $this->getRequest()->getHeaders()['Host'];
    if(!is_string($host)) {
      $host = $host[0];
    }

    $description['base'] = sprintf("%s://%s%s", $scheme, $host, $thing->getHref());

    $description['securityDefinitions'] = [
      'nosec_sc' => [
          'scheme' => 'nosec',
      ],
    ];
    $description['security'] = 'nosec_sc';

    $this->setStatus(200);
    $this->setContentType('application/json');
    $this->write(json_encode($description));
    $this->finish();
  }

}
