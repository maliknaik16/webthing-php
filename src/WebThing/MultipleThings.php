<?php

namespace WebThing;

/**
 * @file
 * Contains WebThing\MultipleThings
 */

/**
 * A container for multiple things.
 */
class MultipleThings {

  /**
   * @array WebThing\Thing
   */
  protected $things;

  /**
   * @var string
   */
  protected $name;

  /**
   * Initialize the container.
   *
   * @param $thing -- a thing to store.
   * @param $name -- the mDNS server name.
   */
  public function __construct(Thing $things, $name) {
    $this->things = $things;
    $this->name = $name;
  }

  /**
   * Get the thing at the given index.
   *
   * @param string
   */
  public function getThing($index) {
    $idx = intval($index);

    if($idx == 0 && $index[0] !== '0') {
    }
    return $this->thing;
  }

  /**
   * Get the list of things.
   */
  public function getThings() {
    return $this->things;
  }

  /**
   * Get the mDNS server name.
   */
  public function getName() {
    return $this->name;
  }
}