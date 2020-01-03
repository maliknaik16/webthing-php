<?php

namespace WebThing;

/**
 * @file
 * Contains WebThing\SingleThing
 */

/**
 * A container for single thing.
 */
class SingleThing implements ThingsInterface {

  /**
   * @var WebThing\Thing
   */
  protected $thing;

  /**
   * Initialize the container.
   *
   * @param $thing a thing to store.
   */
  public function __construct(ThingInterface $thing) {
    $this->thing = $thing;
  }

  /**
   * Get the thing.
   */
  public function getThing($index = '') {
    return $this->thing;
  }

  /**
   * Get the list of things.
   */
  public function getThings() {
    return [
      $this->thing,
    ];
  }

  /**
   * Get the mDNS server name.
   */
  public function getName() {
    return $this->thing->title;
  }
}
