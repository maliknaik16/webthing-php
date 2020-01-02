<?php

namespace WebThing;

interface EventInterface {

  /**
   * Get the event description.
   */
  public function asEventDescription();

  /**
   * Get the thing associated with this event.
   */
  public function getThing();

  /**
   * Get the event's name.
   */
  public function getName();

  /**
   * Get the event's data.
   */
  public function getData();

  /**
   * Get the event's timestamp.
   */
  public function getTime();

}
