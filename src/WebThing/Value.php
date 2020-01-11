<?php

namespace WebThing;

/**
 * @file
 * Value class implementation.
 */

use Evenement\EventEmitter;

/**
 * Represents an property value.
 */
class Value {

  /**
   * The last value of the property.
   *
   * @var mixed
   */
  protected $last_value;

  /**
   * The method that updates the actual value on the thing.
   *
   * @var callable
   */
  protected $value_forwarder;

  /**
   * The Event emitter object.
   *
   * @var \Evenement\EventEmitter;
   */
  protected $emitter;

  /**
   * Initialize the Value object.
   */
  public function __construct($initial_value, $value_forwarder = null) {
    $this->emitter = new EventEmitter();
    $this->last_value = $initial_value;
    $this->value_forwarder = $value_forwarder;
  }

  /**
   * Set the new value for this property.
   */
  public function set($value) {
    if($this->value_forwarder != null) {
      $this->value_forwarder($value);
    }

    $this->notifyOfExternalUpdate($value);
  }

  /**
   * Return the last known value from the thing.
   */
  public function get() {
    return $this->last_value;
  }

  /**
   * Notify observers of a new value.
   */
  public function notifyOfExternalUpdate($new_value) {
    if($new_value != null && $new_value != $this->last_value) {
      $this->last_value = $new_value;
      $this->emitter->emit('valueUpdate', [$new_value]);
    }
  }

  /**
   * Returns the Event emitter.
   */
  public function getEventEmitter() {
    return $this->emitter;
  }
}
