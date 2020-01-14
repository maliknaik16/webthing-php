<?php

namespace WebThing\Server\Handlers;

/**
 * @file
 * Contains ActionsHandler class implementation.
 */

/**
 * Handle a request to '/actions'.
 */
class ActionsHandler extends BaseHandler {

  /**
   * The thing from the ID of the URL path.
   *
   * @var WebThing\ThingInterface
   */
  protected $thing;

  /**
   * {@inheritdoc}
   */
  public function initialize() {
    parent::initialize();
    $thing_id = isset($this->getRouteArgs()['thing_id']) ?: '0';
    $this->thing = $this->getThing($thing_id);
  }

  /**
   * {@inheritdoc}
   */
  public function get() {

    if($this->thing == NULL) {
      $this->sendError(404);
      return;
    }

    $this->setStatus(200);
    $this->setContentType('application/json');
    $this->write(json_encode($this->thing->getActionDescriptions()));
  }

  /**
   * {@inheritdoc}
   */
  public function post() {

    if($this->thing == NULL) {
      $this->sendError(404);
      return;
    }

    try {
      $message = json_decode($this->getRequest()->getBody()->getContents(), true);
    } catch (\Exception $e) {
      $this->sendError(404);
      return;
    }

    $response = [];
    foreach($message as $action_name => $action_params) {
      $input = NULL;

      if(array_key_exists('input', $action_params)) {
        $input = $action_params['input'];
      }

      $action = $this->thing->performAction($action_name, $input);

      // TODO: RECHECK THIS IF CONDITION
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
    $this->setContentType('application/json');
    $this->write(json_encode($response));
  }
}
