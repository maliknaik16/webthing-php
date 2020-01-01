<?php

namespace WebThing;

/**
 * @file
 * Event class implementation.
 */

/**
 * Represents an individual event from a thing.
 */
class Event {

  /**
   * Thing this event belongs to.
   *
   * @var WebThing\Thing
   */
  protected $thing;

  /**
   * The name of the event.
   *
   * @var string
   */
  protected = $name;

  /**
   * The data associated with the event.
   *
   * @var array
   */
  protected = $data;


  /**
   * The time of the event.
   *
   * @var time
   */
  protected = $time;

  /**
   * Initialize the object.
   */
  public function __construct(Thing $thing, $name, $data = null) {
    $this->thing = $thing;
    $this->name = $name;
    $this->data = $data;
    $this->time = timestamp();
  }

  /**
   * Get the event description.
   */
  public function asEventDescription() {
    $description = [
      $this->name => [
        'timestamp' => $this->time,
      ],
    ];

    if(!empty($data)) {
      $description[$this->name]['data'] = $this->data;
    }

    return $description;
  }

  /**
   * Get the thing associated with this event.
   */
  public function getThing() {
    return $this->thing;
  }

  /**
   * Get the event's name.
   */
  public function getName() {
    return $this->name;
  }

  /**
   * Get the event's data.
   */
  public function getData() {
    return $this->data;
  }

  /**
   * Get the event's timestamp.
   */
  public function getTime() {
    return $this->time;
  }

}
