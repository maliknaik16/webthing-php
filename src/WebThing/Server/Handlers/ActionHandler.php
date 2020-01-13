<?php

namespace WebThing\Server\Handlers;

/**
 * @file
 * Contains ActionHandler class implementation.
 */

/**
 * Handle a request to '/actions/<action_name>'.
 */
class ActionHandler extends BaseHandler {

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
   * {@inheritdoc}
   */
  public function initialize() {
    parent::initialize();
    $route_args = $this->getRouteArgs();
    $thing_id = array_key_exists('thing_id', $route_args) ? $route_args['thing_id'] : '0';

    $this->thing = $this->getThing($thing_id);
    $this->action_name = array_key_exists('action_name', $route_args) ? $route_args['action_name'] : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function get() {

    if($this->thing == NULL) {
      $this->sendError(404);
      return;
    }

    $this->setContentType('application/json');
    $this->write(json_encode($this->thing->getActionDescriptions($this->action_name)));
  }

  /**
   * {@inheritdoc}
   */
  public function post() {

    if($this->thing == NULL) {
      $this->sendError(404);
      return;
    }

    $message = json_decode($this->getRequest()->getParsedBody());

    if($message === NULL && json_last_error() !== JSON_ERROR_NONE) {
      $this->setStatus(400);
      return;
    }

    $response = [];

    foreach($message as $name => $action_params) {
      if($name != $action_name) {
        continue;
      }

      $input = NULL;
      if(in_array('input', $action_params)) {
        $input = $action_params['input'];
      }

      $action = $this->thing->performAction($name, $input);

      if($action) {
        $response = array_merge($response, $action->asActionDescription());

        // Start the action
        // Implemented logic to run action in next iteration
        // TODO: Check whether it works as expected.
        $this->getLoop()->futureTick(function () use ($action) {
          $action->start();
        });
      }
    }

    $this->setStatus(201);
    $this->write(json_encode($response));
  }
}
