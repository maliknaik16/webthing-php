<?php

namespace WebThing;

/**
 * @file
 * Action class implementation.
 */

/**
 * Represents an individual action on a thing.
 */
class Action implements ActionInterface {

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
  protected $thing;

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
  public function __construct(Thing $thing, $id, $name, $input) {
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
   * {@inheritdoc}
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
   * {@inheritdoc}
   */
  public function setHrefPrefix($prefix) {
    $this->href_prefix = $prefix;
  }

  /**
   * {@inheritdoc}
   */
  public function getId() {
    return $this->id;
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
  public function getHref() {
    return $this->href_prefix . $this->href;
  }

  /**
   * {@inheritdoc}
   */
  public function getStatus() {
    return $this->status;
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
  public function getTimeRequested() {
    return $this->time_requested;
  }

  /**
   * {@inheritdoc}
   */
  public function getTimeCompleted() {
    return $this->time_completed;
  }

  /**
   * {@inheritdoc}
   */
  public function getInput() {
    return $this->input;
  }

  /**
   * {@inheritdoc}
   */
  public function start() {
    $this->status = 'pending';
    $this->thing->actionNotify($this);
    $this->performAction();
    $this->finish();
  }

  /**
   * {@inheritdoc}
   */
  public function performAction() {
  }

  /**
   * {@inheritdoc}
   */
  public function cancel() {
  }

  /**
   * {@inheritdoc}
   */
  public function finish() {
    $this->status = 'completed';
    $this->time_completed = timestamp();
    $this->thing->actionNotify($this);
  }
}
