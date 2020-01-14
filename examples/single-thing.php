<?php

require dirname(__DIR__) . '/vendor/autoload.php';

use WebThing\Action;
use WebThing\Event;
use WebThing\Property;
use WebThing\SingleThing;
use WebThing\Thing;
use WebThing\Value;
use WebThing\Server\WebThingServer;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;

//$test = new \WebThing\Server\Handlers\PropertyHandler(NULL, []);

class OverheatedEvent extends Event {
  public function __construct($thing, $data) {
    parent::__construct($thing, 'overheated', $data);
  }
}

class FadeAction extends Action {
  public function __construct($thing, $input) {

    try {
      $uuid = Uuid::uuid4()->toString();
    }catch(UnsatisfiedDependencyException $e) {
      echo 'Caught exception: ' . $e->getMessage() . PHP_EOL;
    }

    parent::__construct($thing, $uuid, 'fade', $input);
  }

  public function performAction() {
    sleep($this->input['duration'] / 1000);
    $this->thing->setProperty('brightness', $this->input['brightness']);
    $this->thing->addEvent(new OverheatedEvent($this->thing, 102));
  }
}

function make_thing() {
  $thing = new Thing(
    'urn:dev:ops:my-lamp-1234',
    'My Lamp',
    ['OnOffSwitch', 'Light'],
    'A web connected lamp'
  );

  $thing->addProperty(new Property(
    $thing,
    'on',
    new Value(TRUE),
    [
      '@type' => 'OnOffProperty',
      'title' => 'On/Off',
      'type' => 'boolean',
      'description' => 'Whether the lamp is turned on',
    ])
  );

  $thing->addProperty(new Property(
    $thing,
    'brightness',
    new Value(50),
    [
      '@type' => 'BrightnessProperty',
      'title' => 'Brightness',
      'type' => 'integer',
      'description' => 'The level of light from 0-100',
      'minimum' => 0,
      'maximum' => 100,
      'unit' => 'percent',
    ])
  );

  $thing->addAvailableAction(
    'fade',
    [
      'title' => 'Fade',
      'description' => 'Fade the lamp to a given level',
      'input' => [
        'type' => 'object',
        'required' => [
          'brightness',
          'duration',
        ],
        'properties' => [
          'brightness' => [
            'type' => 'integer',
            'minimum' => 0,
            'maximum' => 100,
            'unit' => 'percent',
          ],
          'duration' => [
            'type' => 'integer',
            'minimum' => 1,
            'unit' => 'milliseconds',
          ],
        ],
      ],
    ],
    'FadeAction'
  );

  $thing->addAvailableEvent(
    'overheated',
    [
      'description' => 'The lamp has exceeded its safe operating temperature',
      'type' => 'number',
      'unit' => 'degree celsius',
    ]
  );

  return $thing;
}


function run_server() {
  $thing = make_thing();

  $server = new WebThingServer(new SingleThing($thing), '127.0.0.1', 8888, 8081);

  $server->start();
}


run_server();
