# Web Things PHP

[![travis](https://api.travis-ci.org/maliknaik16/webthing-php.svg?branch=master)](https://travis-ci.com/maliknaik16/webthing-php)
[![GitHub forks](https://img.shields.io/github/forks/maliknaik16/webthing-php)](https://github.com/maliknaik16/webthing-php/network/)
[![GitHub version](https://badge.fury.io/gh/maliknaik16%2Fwebthing-php.svg)](https://badge.fury.io/gh/maliknaik16%2Fwebthing-php)
[![Source Code](https://img.shields.io/badge/source-maliknaik16%2Fwebthing--php-blue?style=flat-square)](https://github.com/maliknaik16/webthing-php)
[![PHP Version](https://img.shields.io/badge/PHP-7.1%2B-orange)](https://php.net)
[![Software License](https://img.shields.io/badge/license-MPL--2.0-green?style=flat-square)](https://github.com/maliknaik16/webthing-php/blob/master/LICENSE.txt)

Implementation of an HTTP [Web Thing](https://iot.mozilla.org/wot/). This library is compatible with PHP 7.1+.

# Installation

The ``webthing`` can be installed using ``composer`` via the following command:

```bash
composer require webthing/webthing:^0.0.1
```

# Running the Example
The following commands clones this repository and install all dependencies using the composer and run the `single-thing.php` example.
```bash
git clone https://github.com/maliknaik16/webthing-php.git
cd webthing-php
composer install
php examples/single-thing.php
```

# Usage

The following sections will describe the usage of classes found in this libraray.

## Thing
The `Thing` class takes 4 arguments and they are as follows:
  - `$id` - the unique ID for the thing.
  - `$thing` - The thing's title.
  - `$type` - The thing's type.
  - `$description` - The description of the thing.

Creating a Thing:
```php
$thing = new Thing(
  'urn:dev:ops:my-lamp-1234',
  'My Lamp',
  ['OnOffSwitch', 'Light'],
  'A web connected lamp'
);
```

# License

Mozilla Public License Version 2.0
