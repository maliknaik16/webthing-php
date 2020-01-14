<?php

namespace WebThing;

/**
 * @file
 * Event class implementation.
 */

/**
 * Represents an individual event from a thing.
 */
class Event implements EventInterface {

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
  protected $name;

  /**
   * The data associated with the event.
   *
   * @var array
   */
  protected $data;


  /**
   * The time of the event.
   *
   * @var time
   */
  protected $time;

  /**
   * Initialize the object.
   */
  public function __construct(ThingInterface $thing, $name, $data = NULL) {
    $this->thing = $thing;
    $this->name = $name;
    $this->data = $data;
    $this->time = date("c", time());
  }

  /**
   * {@inheritdoc}
   */
  public function asEventDescription() {
    $description = [
      $this->name => [
        'timestamp' => $this->time,
      ],
    ];

    if(!is_null($this->data)) {
      $description[$this->name]['data'] = $this->data;
    }

    return $description;
  }

  /**
   * {@inheritdoc}
   */
  public function getThing() {
    return $this->thing;
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->name;
  }

  /**
   * {@inheritdoc}
   */
  public function getData() {
    return $this->data;
  }

  /**
   * {@inheritdoc}
   */
  public function getTime() {
    return $this->time;
  }

}
