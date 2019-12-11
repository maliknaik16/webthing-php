<?php

namespace WebThing;

/**
 * @file
 * High level Action base class implementation.
 */

/**
 * Represents an individual action on a thing.
 */
class Action {

  /**
   * ID of this action.
   *
   * @var string
   */
  protected $id;

  /**
   * The Thing this action belongs to.
   *
   * @var Thing
   */
  protected = $thing;

  /**
   * Name of this action.
   *
   * @var string
   */
  protected $name;

  /**
   * Any action inputs
   *
   * @var string
   */
  protected $input;

  /**
   * The prefix of the href.
   *
   * @var string
   */
  protected $hrefPrefix;

  /**
   * The actual href.
   *
   * @var string
   */
  protected $href;

  /**
   * The status of the action.
   *
   * @var string
   */
  protected $status;

  /**
   * The time this action was requested.
   *
   * @var timestamp
   */
  protected $timeRequested;

  /**
   * The time this action was completed.
   *
   * @var timestamp
   */
  protected $timeCompleted;

  /**
   * Initialize the object.
   */
  public function __construct($id, Thing $thing, $name, $input) {
    $this->id = $id;
    $this->thing = $thing;
    $this->name = $name;
    $this->input = $input;
    $this->hrefPrefix = '';
    $this->href = sprintf('/actions/%s/%s', $this->name, $this->id);
    $this->status = 'created';
    $this->timeRequested = timestamp();
    $this->timeCompleted = NULL;
  }
}
