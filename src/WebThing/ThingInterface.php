<?php

namespace WebThing;

interface ThingInterface {

  /**
   * Return the thing state as a Thing Description.
   */
  public function asThingDescription();

  /**
   * Get the href of this thing
   */
  public function getHref();

  /**
   * Get the UI href.
   */
  public function getUiHref();

  /**
   * Set the prefix of any hrefs associated with this thing.
   */
  public function setHrefPrefix($prefix);

  /**
   * Set the href of this thing's custom UI.
   */
  public function setUiHref($href);

  /**
   * Get the ID of the thing.
   */
  public function getId();

  /**
   * Get the title of the thing.
   */
  public function getTitle();

  /**
   * Get the type context of the thing.
   */
  public function getContext();

  /**
   * Get the type(s) of the thing.
   */
  public function getType();

  /**
   * Get the description of the thing.
   */
  public function getDescription();

  /**
   * Get the thing's properties as an associative array.
   */
  public function getPropertyDescriptions();

  /**
   * Get the thing's actions as an array.
   */
  public function getActionDescriptions($action_name = '');

  /**
   * Get the thing's events as an array.
   */
  public function getEventDescriptions($event_name = '');

  /**
   * Add a property to this thing.
   */
  public function addProperty($property);

  /**
   * Remove a property from this thing.
   */
  public function removeProperty($property);

  /**
   * Find a property by name.
   */
  public function findProperty($property_name);

  /**
   * Get a property's value.
   */
  public function getProperty($property_name);

  /**
   * Get a mapping of all properties and their values.
   */
  public function getProperties();

  /**
   * Determine whether or not this thing has a given property.
   */
  public function hasProperty($property_name);

  /**
   * Set a property value.
   */
  public function setProperty($property_name, $value);

  /**
   * Get an action.
   */
  public function getAction($action_name, $action_id);

  /**
   * Add a new event and notify subscribers.
   */
  public function addEvent($event);

  /**
   * Add an available event.
   */
  public function addAvailableEvent($name, $metadata);

  /**
   * Perform an action on the thing.
   */
  public function performAction($action_name, $input = null);

  /**
   * Remove an existing action.
   */
  public function removeAction($action_name, $action_id);

  /**
   * Add an available action.
   */
  public function addAvailableAction($name, $metadata, $cls);

  /**
   * Add a new websocket subscriber.
   */
  public function addSubscriber($ws);

  /**
   * Remove a websocket subscriber.
   */
  public function removeSubscriber($ws);

  /**
   * Add a new websocket subscriber to an event.
   */
  public function addEventSubscriber($name, $ws);

  /**
   * Remove a websocket subscriber from an event.
   */
  public function removeEventSubscriber($name, $ws);

  /**
   * Notify all subscribers of a property change.
   */
  public function propertyNotify($property);

  /**
   * Notify all subscribers of an action status change.
   */
  public function actionNotify($action);

  /**
   * Notify all subscribers of an event.
   */
  public function eventNotify($event);
}
