<?php

require dirname(__DIR__) . '/vendor/autoload.php';

use WebThing\Action;
use WebThing\Event;
use WebThing\Property;
use WebThing\MultipleThings;
use WebThing\Thing;
use WebThing\Value;
use WebThing\Server\WebThingServer;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;

$display = function($v) {
  echo $v;
};
$loop = React\EventLoop\Factory::create();

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

    parent::__construct($uuid, $thing, 'fade', $input);
  }

  public function performAction() {
    sleep($this->input['duration'] / 1000);
    $this->thing->setProperty('brightness', $this->input['brightness']);
    $this->thing->addEvent(new OverheatedEvent($this->thing, 102));
  }
}


/**
 * A dimmable light that logs received commands to stdout.
 */
class ExampleDimmableLight extends Thing {
  public function __construct() {
    parent::__construct(
      'urn:dev:ops:my-lamp-1234',
      'My Lamp',
      ['OnOffSwitch', 'Light'],
      'A web connected lamp'
    );

    $this->addProperty(new Property(
      $this,
      'on',
      new Value(TRUE, function($v) {
        echo 'On-State is now ' . $v;
      }),
      [
        '@type' => 'OnOffProperty',
        'title' => 'On/Off',
        'type' => 'boolean',
        'description' => 'Whether the lamp is turned on',
     ])
    );

    $this->addProperty(new Property(
      $this,
      'brightness',
      new Value(50, function($v) {
        echo 'Brightness is now ' . $v;
      }),
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

    $this->addAvailableAction(
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

    $this->addAvailableEvent(
      'overheated',
      [
        'description' => 'The lamp has exceeded its safe operating temperature',
        'type' => 'number',
        'unit' => 'degree celsius',
      ]
    );
  }
}

class FakeGpioHumiditySensor extends Thing {

  private $level;

  public function __construct() {
    global $loop;

    parent::__construct(
      'urn:dev:ops:my-lamp-1234',
      'My Lamp',
      ['OnOffSwitch', 'Light'],
      'A web connected lamp'
    );

    $this->level = new Value(0.0);
    $this->addProperty(new Property(
      $this,
      'level',
      $this->level,
      [
        '@type' => 'LevelProperty',
        'title' => 'Humidity',
        'type' => 'number',
        'description' => 'The current humidity in %',
        'minimum' => 0,
        'maximum' => 100,
        'unit' => 'percent',
        'readOnly' => TRUE,
      ])
    );
    echo 'Starting the sensor update looping task' . PHP_EOL;

    $level = $this->level;
    $loop->addPeriodicTimer(7, function() use ($level) {
      $new_level = $this->readFromGpio();
      printf("Setting new humidity level: %s\n", $new_level);
      $level->notifyOfExternalUpdate($new_level);
    });
  }

  /**
   * Mimic an actual sensor updating its reading every couple seconds.
   */
  public function readFromGpio() {
    return abs(70.0 * rand() * (-0.5 + rand()));
  }
}

function run_server() {
  global $loop;

  // Create a thing that represents a dimmable light
  $light = new ExampleDimmableLight();

  // Create a thing that represents a humidity sensor
  $sensor = new FakeGpioHumiditySensor();

  // If adding more than one thing, use MultipleThings() with a name.
  // In the single thing case, the thing's name will be broadcast.
  $server = new WebThingServer(new MultipleThings([$light, $sensor], 'LightAndTempDevice'), '127.0.0.1', 8080, 8081, [], NULL, '', $loop);

  // Start the server.
  $server->start();
}

run_server();
