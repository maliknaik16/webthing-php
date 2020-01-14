<?php

namespace WebThing\Server\Handlers;

/**
 * @file
 * Contains PropertyHandler class implementation.
 */

/**
 * Handle a request to '/properties/<property>'.
 */
class PropertyHandler extends BaseHandler {

  /**
   * The thing from the ID of the URL path.
   *
   * @var WebThing\ThingInterface
   */
  protected $thing;

  /**
   * Name of the property from the URL path.
   *
   * @var mixed
   */
  protected $property_name;

  /**
   * {@inheritdoc}
   */
  public function initialize() {
    parent::initialize();
    $thing_id = array_key_exists('thing_id', $this->getRouteArgs()) ? $this->getRouteArgs()['thing_id'] : '0';

    $this->thing = $this->getThing($thing_id);
    $this->property_name = isset($this->getRouteArgs()['property_name']) ? $this->getRouteArgs()['property_name'] : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function get() {

    if($this->thing == NULL) {
      $this->sendError(404);
      return;
    }

    if($this->thing->hasProperty($this->property_name)) {
      $this->setStatus(200);
      $this->setContentType('application/json');
      $this->write(json_encode([
        $this->property_name => $this->thing->getProperty($this->property_name),
      ]));
    }else{
      $this->sendError(404);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function put() {

    if($this->thing == NULL) {
      $this->sendError(404);
      return;
    }

    $args = json_decode($this->getRequest()->getBody()->getContents(), true);

    if($args === NULL && json_last_error() !== JSON_ERROR_NONE) {
      $this->setStatus(400);
      return;
    }

    if(!array_key_exists($this->property_name, $args)) {
      $this->setStatus(400);
      return;
    }

    if($this->thing->hasProperty($this->property_name)) {
      try {
        $this->thing->setProperty($this->property_name, $args[$this->property_name]);
      } catch(\Exception $e) {
        $this->setStatus(400);
        return;
      }
      $this->setStatus(200);
      $this->setContentType('application/json');
      $this->write(json_encode([
        $this->property_name => $this->thing->getProperty($this->property_name),
      ]));

    } else {
      $this->setStatus(400);
    }
  }
}
