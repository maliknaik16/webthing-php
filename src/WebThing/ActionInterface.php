<?php

namespace WebThing;

interface ActionInterface {

  /**
   * Get the action description.
   */
  public function asActionDescription();

  /**
   * Set the prefix of any hrefs associated with this action.
   */
  public function setHrefPrefix($prefix);

  /**
   * Get this action's id.
   */
  public function getId();

  /**
   * Get this action's name.
   */
  public function getName();

  /**
   * Get this action's href.
   */
  public function getHref();

  /**
   * Get this action's status.
   */
  public function getStatus();

  /**
   * Get the thing associated with this action.
   */
  public function getThing();

  /**
   * Get the time the action was requested.
   */
  public function getTimeRequested();

  /**
   * Get the time the action was completed.
   */
  public function getTimeCompleted();

  /**
   * Get the inputs for this action.
   */
  public function getInput();

  /**
   * Start performing the action.
   */
  public function start();

  /**
   * Override this method with the code necessary to perform the action.
   */
  public function performAction();

  /**
   * Override this method with the code necessary to cancel the action.
   */
  public function cancel();

  /**
   * Finish performing the action.
   */
  public function finish();
}
