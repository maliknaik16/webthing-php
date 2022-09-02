# Web of Things

[![travis](https://api.travis-ci.org/maliknaik16/webthing-php.svg?branch=master)](https://travis-ci.com/maliknaik16/webthing-php)
[![GitHub forks](https://img.shields.io/github/forks/maliknaik16/webthing-php)](https://github.com/maliknaik16/webthing-php/network/)
[![GitHub version](https://badge.fury.io/gh/maliknaik16%2Fwebthing-php.svg)](https://badge.fury.io/gh/maliknaik16%2Fwebthing-php)
[![Source Code](https://img.shields.io/badge/source-maliknaik16%2Fwebthing--php-blue?style=flat-square)](https://github.com/maliknaik16/webthing-php)
[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-orange)](https://php.net)
[![Software License](https://img.shields.io/badge/license-MPL--2.0-green?style=flat-square)](https://github.com/maliknaik16/webthing-php/blob/master/LICENSE.txt)

Implementation of an HTTP [Web Thing](https://iot.mozilla.org/wot/). This library is compatible with PHP 7.4+.

# Installation

The ``webthing`` can be installed using ``composer`` via the following command:

```bash
composer require webthing/webthing:^0.0.1
```

# Running the Example
The following list of commands clones this repository and installs all dependencies using the composer and runs the `single-thing.php` example.
```bash
git clone https://github.com/maliknaik16/webthing-php.git
cd webthing-php
composer install
php examples/single-thing.php
```

# Example Implementation

In this code-walkthrough we will set up a dimmable light and a humidity sensor (both using fake data, of course). Both working examples can be found in [here](https://github.com/maliknaik16/webthing-php/tree/master/examples).

## Dimmable Light

Imagine you have a dimmable light that you want to expose via the web of things API. The light can be turned on/off and the brightness can be set from 0% to 100%. Besides the name, description, and type, a [Light](https://iot.mozilla.org/schemas/#Light) is required to expose two properties:

  - ``on``: the state of the light, whether it is turned on or off

    - Setting this property via a ``PUT {"on": true/false}`` call to the REST API toggles the light.

  - ``brightness``: the brightness level of the light from 0-100%

    - Setting this property via a PUT call to the REST API sets the brightness level of this light.

First we create a new Thing:

```php
$light = new Thing(
  'urn:dev:ops:my-lamp-1234',
  'My Lamp',
  ['OnOffSwitch', 'Light'],
  'A web connected lamp'
);
```

Now we can add the required properties.

The ``on`` property reports and sets the on/off state of the light. For this, we need to have a ``Value`` object which holds the actual state and also a method to turn the light on/off. For our purposes, we just want to log the new state if the light is switched on/off.

```php
$light->addProperty(new Property(
  $light,
  'on',
  new Value(TRUE, function($v) {
    echo "On-State is now " . $v . "\n";
  }),
  [
    '@type' => 'OnOffProperty',
    'title' => 'On/Off',
    'type' => 'boolean',
    'description' => 'Whether the lamp is turned on',
  ])
);
```

The ``brightness`` property reports the brightness level of the light and sets the level. Like before, instead of actually setting the level of a light, we just log the level.

```php
$light->addProperty(new Property(
  $light,
  'brightness',
  new Value(50, function($v) {
    echo "Brightness is now " . $v . "\n";
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
```

Now we can add our newly created thing to the server and start it:

```php
// If adding more than one thing, use MultipleThings() with a name.
// In the single thing case, the thing's name will be broadcast.
$server = new WebThingServer(new SingleThing($thing), '127.0.0.1', 8888, 8081);

$server->start();
$server->startWebSocket();
```
This will start the server, making the light available via the WoT REST API and announcing it as a discoverable resource on your local network via mDNS.

## Sensor

Let's now also connect a humidity sensor to the server we set up for our light.

A [MultiLevelSensor](https://iot.mozilla.org/schemas/#MultiLevelSensor) (a sensor that returns a level instead of just on/off) has one required property (besides the name, type, and optional description): ``level``. We want to monitor this property and get notified if the value changes.

First we create a new Thing:

```php
$sensor = new Thing(
 'urn:dev:ops:my-humidity-sensor-1234',
  'My Humidity Sensor',
  ['MultiLevelSensor'],
  'A web connected humidity sensor'
);
```

Then we create and add the appropriate property:

  - ``level``: tells us what the sensor is actually reading

    - Contrary to the light, the value cannot be set via an API call, as it wouldn't make much sense, to SET what a sensor is reading. Therefore, we are creating a **readOnly** property.

      ```php
      $level = new Value(0.0);
      $sensor->addProperty(new Property(
        $sensor,
        'level',
        $level,
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
      ```

Now we have a sensor that constantly reports 0%. To make it usable, we need a thread or some kind of input when the sensor has a new reading available. For this purpose we start a thread that queries the physical sensor every few seconds. For our purposes, it just calls a fake method.

```php
// $level is a `Value` object.
// $loop is a `React\EventLoop\Factory` object.
$loop->addPeriodicTimer(7, function() use ($level) {
  $new_level = readFromGpio();
  printf("Setting new humidity level: %s\n", $new_level);
  $level->notifyOfExternalUpdate($new_level);
});


function readFromGpio() {
  return abs(70.0 * rand() * (-0.5 + rand()));
}
```
This will update our ``Value`` object with the sensor readings via the ``$level->notifyOfExternalUpdate(readFromGpio());`` call. The ``Value`` object now notifies the property and the thing that the value has changed, which in turn notifies all websocket listeners.

# Resources
  - [https://iot.mozilla.org/wot](https://iot.mozilla.org/wot)
  - [https://iot.mozilla.org/framework/](https://iot.mozilla.org/framework/)
  - [https://iot.mozilla.org/gateway/](https://iot.mozilla.org/gateway/)
  - [https://www.w3.org/WoT/IG/](https://www.w3.org/WoT/IG/)

# License

Mozilla Public License Version 2.0
