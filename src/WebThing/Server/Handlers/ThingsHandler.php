<?php

namespace WebThing\Server\Handlers;

/**
 * @file
 * Contains ThingsHandler class implementation.
 */

use WebThing\Thing;

/**
 * Handle a request to '/' when the server manages multiple things.
 */
class ThingsHandler extends BaseHandler {

  /**
   * Handle a GET request.
   */
  public function get() {
    $scheme = $this->getRequest()->getUri()->getScheme();
    $ws = $scheme == 'https' ? 'wss' : 'ws';
    $host = $this->getRequest()->getHeaders()['Host'];
    if(!is_string($host)) {
      $host = $host[0];
    }
    $ws_href = sprintf("%s://%s", $ws, $host);

    $descriptions = [];

    foreach($this->things->getThings() as $thing) {
      $description = $thing->asThingDescription();
      $description['href'] = $thing->getHref();
      $description['link'][] = [
        'rel' => 'alternate',
        'href' => sprintf("%s%s", $ws_href, $thing->getHref()),
      ];
      $description['base'] = sprintf("%s://%s%s", $scheme, $host, $thing->getHref());
      $description['securityDefinitions'] = [
        'nosec_sc' => [
          'scheme' => 'nosec',
        ],
      ];
      $description['security'] = 'nosec_sc';

      array_push($descriptions, $description);
    }

    $this->setStatus(200);
    $this->setContentType('application/json');
    $this->write(json_encode($descriptions, JSON_UNESCAPED_SLASHES));
  }
}
