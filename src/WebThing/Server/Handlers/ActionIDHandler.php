<?php

namespace WebThing\Server\Handlers;

/**
 * @file
 * Contains ActionIDHandler class implementation.
 */

/**
 * Handle a request to '/actions/<action_name>/<action_id>'.
 */
class ActionIDHandler extends BaseHandler {

  /**
   * The thing from the ID of the URL path.
   *
   * @var WebThing\ThingInterface
   */
  protected $thing;

  /**
   * Name of the action from the URL path.
   *
   * @var mixed
   */
  protected $action_name;

  /**
   * The action ID from the URL path.
   *
   * @var mixed
   */
  protected $action_id;

  /**
   * {@inheritdoc}
   */
  public function initialize() {
    parent::initialize();
    $thing_id = isset($this->getRouteArgs()['thing_id']) ?: '0';

    $this->thing = $this->getThing($thing_id);
    $this->action_name = isset($this->getRouteArgs()['action_name']) ?: NULL;
    $this->action_id = isset($this->getRouteArgs()['action_id']) ?: NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function get() {

    if($this->thing == NULL) {
      $this->sendError(404);
      return;
    }

    $action = $this->thing->getAction($this->action_name, $this->action_id);

    if($action === NULL) {
      $this->setStatus(404);
      return;
    }

    $this->setContentType('application/json');
    $this->write(json_encode($action->asActionDescription()));
  }

  /**
   * {@inheritdoc}
   */
  public function put() {
    // TODO: this is not yet defined in the spec

    if ($this->thing == NULL) {
      $this->sendError(404);
      return;
    }

    $this->setStatus(200);
  }

  /**
   * {@inheritdoc}
   */
  public function delete() {

    if ($this->thing == NULL) {
      $this->sendError(404);
      return;
    }

    if ($this->thing->removeAction($this->action_name, $this->action_id)) {
      $this->setStatus(204);
    } else {
      $this->setStatus(404);
    }
  }
}
