<?php

namespace WebThing;

/**
 * @file
 * Contains WebThing\SingleThing
 */

/**
 * A container for single thing.
 */
class SingleThing {

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
   * Get the thing at the given index.
   */
  public function getThing() {
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
