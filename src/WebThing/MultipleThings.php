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
   * @var WebThing\Thing[]
   */
  protected $things;

  /**
   * @var string
   */
  protected $name;

  /**
   * Initialize the container.
   *
   * @param $thing
   *  a thing to store.
   * @param $name
   *  the mDNS server name.
   */
  public function __construct(ThingInterface $things, $name) {
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

    if($idx == 0 && !isset($this->things[$idx])) {
      return null;
    }

    if($idx < 0 || $idx >= count($this->things)) {
      return null;
    }

    return $this->things[$idx];
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
