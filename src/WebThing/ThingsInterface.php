<?php

namespace WebThing;

/**
 * @file
 * Contains WebThing\ThingsInterface
 */

/**
 * An interface for SingleThing and MultipleThings.
 */
interface ThingsInterface {

  /**
   * Get the thing at the given index.
   *
   * @param string
   *
   * @return WebThing\ThingInterface
   */
  public function getThing($index);

  /**
   * Get the list of things.
   *
   * @return WebThing\ThingInterface[]
   */
  public function getThings();

  /**
   * Get the mDNS server name.
   *
   * @return string
   */
  public function getName();
}
