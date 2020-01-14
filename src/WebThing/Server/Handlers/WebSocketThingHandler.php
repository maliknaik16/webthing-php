<?php

namespace WebThing\Server\Handlers;

/**
 * @file
 * Contains WebSocketThingHandler class implementation.
 */

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

use Psr\Http\Message\RequestInterface;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7 as gPsr;

/**
 * Handle a request to '/'.
 */
class WebSocketThingHandler implements MessageComponentInterface {

  /**
   * List of things managed by this server.
   *
   * @var WebThing\Thing
   */
  protected $things;

  /**
   * The Thing for this request.
   *
   * @var WebThing\Thing
   */
  protected $thing;

  /**
   * List of allowed host names.
   *
   * @var array
   */
  protected $hosts;

  /**
   * Initialize the object.
   */
  public function __construct($things, $hosts, $loop) {
    $this->things = $things;
    $this->hosts = $hosts;
    $this->loop = $loop;
  }

  /**
   * {@inheritdoc}
   */
  public function onOpen(ConnectionInterface $conn) {
    parse_str($conn->httpRequest->getUri()->getQuery(), $requestQuery);
    $host = $conn->httpRequest->getHeaders()['Host'];

    $thing_id = array_key_exists('thing_id', $requestQuery) ? $requestQuery['thing_id'] : '0';
    $this->thing = $this->getThing($thing_id);

    $not_found = new Response(404);

    if(!is_string($host)) {
      $host = $host[0];
    }

    if($this->thing == NULL) {
      $conn->send(gPsr\str($not_found));
      $conn->close();
      return;
    }

    if(!is_null($host) && in_array($host, $this->hosts)) {
      $this->thing->addSubscriber($conn);
      return;
    }

    $response = new Response(403);
    $conn->send(gPsr\str($response));
    $conn->close();
  }

  /**
   * {@inheritdoc}
   */
  public function onMessage(ConnectionInterface $from, $msg) {
    $message = json_decode($msg, true);


    if(($message === NULL || empty($message)) && json_last_error() !== JSON_ERROR_NONE && $from !== NULL) {
      $from->send($this->jsonErrorResponse('400 Bad Request', 'Parsing request failed'));
      return;
    }

    if(!array_key_exists('messageType', $message) && !array_key_exists('data', $message)) {
      $from->send($this->jsonErrorResponse('400 Bad Request', 'Invalid message'));

      return;
    }


    $msgType = $message['messageType'];

    if($msgType == 'setProperty') {
      foreach($message['data'] as $property_name => $property_value) {
        try {
          $this->thing->setProperty($property_name, $property_value);
        } catch(\Exception $e) {
          $from->send($this->jsonErrorResponse('400 Bad Request', $e->getMessage()));
        }
      }
    }else if($msgType == 'requestAction') {
      foreach($message['data'] as $action_name => $action_params) {
        $input = NULL;

        if(array_key_exists('input', $action_params)) {
          $input = $action_params['input'];
        }

        $action = $this->thing->performAction($action_name, $input);

        // TODO: TEST THE FUTURE LOOP LATER
        if($action) {
          $this->loop->futureTick(function () use ($action) {
            $action->start();
          });
        }else{
          $from->send($this->jsonErrorResponse('400 Bad Request', 'Invalid action request', TRUE, $message));
        }
      }
    }else if($msgType == 'addEventSubscription') {
      foreach($message['data'] as $event_name => $event_) {
        $this->thing->addEventSubscriber($event_name, $from);
      }
    }else{
      $from->send($this->jsonErrorResponse('400 Bad Request', 'Unknown messageType: ' . $msgType, TRUE, $message));

      return;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function onClose(ConnectionInterface $conn) {
    $this->thing->removeSubscriber($conn);
  }

  /**
   * {@inheritdoc}
   */
  public function onError(ConnectionInterface $conn, \Exception $e) {
    echo 'Caught Exception: ' . $e->getMessage();
  }

  /**
   * Get the thing this request is for.
   */
  public function getThing($thing_id) {
    return $this->things->getThing($thing_id);
  }

  /**
   * JSON Error message response
   */
  private function jsonErrorResponse($statusMsg, $msg, $request = FALSE, $requestMsg = '') {
    $json_message = [
      'messageType' => 'error',
      'data' => [
        'status' => $statusMsg,
        'message' => $msg,
      ],
    ];

    if($request) {
      $json_message['data']['request'] = $requestMsg;
    }

    return json_encode($json_message);
  }
}
