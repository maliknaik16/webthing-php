<?php

namespace WebThing;

/**
 * @file
 * Action class implementation.
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
  protected $href_prefix;

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
  protected $time_requested;

  /**
   * The time this action was completed.
   *
   * @var timestamp
   */
  protected $time_completed;

  /**
   * Initialize the object.
   */
  public function __construct($id, Thing $thing, $name, $input) {
    $this->id = $id;
    $this->thing = $thing;
    $this->name = $name;
    $this->input = $input;
    $this->href_prefix = '';
    $this->href = sprintf('/actions/%s/%s', $this->name, $this->id);
    $this->status = 'created';
    $this->time_requested = timestamp();
    $this->time_completed = null;
  }

  /**
   * Get the action description.
   */
  public function asActionDescription() {
    $description = [
      $this->name => [
        'href' => $this->href_prefix . $this->href,
        'timeRequested' => $this->time_requested,
        'status' => $this->status,
      ],
    ];

    if(!empty($this->input)) {
      $description[$this->name]['input'] = $this->input;
    }

    if(!empty($this->time_completed)) {
      $description[$this->name]['timeCompleted'] = $this->time_completed;
    }

    return $description;
  }

  /**
   * Set the prefix of any hrefs associated with this action.
   */
  public function setHrefPrefix($prefix) {
    $this->href_prefix = $prefix;
  }

  /**
   * Get this action's id.
   */
  public function getId() {
    return $this->id;
  }

  /**
   * Get this action's name.
   */
  public function getName() {
    return $this->name;
  }

  /**
   * Get this action's href.
   */
  public function getHref() {
    return $this->href_prefix . $this->href;
  }

  /**
   * Get this action's status.
   */
  public function getStatus() {
    return $this->status;
  }

  /**
   * Get the thing associated with this action.
   */
  public function getThing() {
    return $this->thing;
  }

  /**
   * Get the time the action was requested.
   */
  public function getTimeRequested() {
    return $this->time_requested;
  }

  /**
   * Get the time the action was completed.
   */
  public function getTimeCompleted() {
    return $this->time_completed;
  }

  /**
   * Get the inputs for this action.
   */
  public function getInput() {
    return $this->input;
  }

  /**
   * Start performing the action.
   */
  public function start() {
    $this->status = 'pending';
    $this->thing->actionNotify($this);
    $this->performAction();
    $this->finish();
  }

  /**
   * Override this method with the code necessary to perform the action.
   */
  public function performAction() {
  }

  /**
   * Override this method with the code necessary to cancel the action.
   */
  public function cancel() {
  }

  /**
   * Finish performing the action.
   */
  public function finish() {
    $this->status = 'completed';
    $this->time_completed = timestamp();
    $this->thing->actionNotify($this);
  }
}
