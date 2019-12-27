<?php

namespace WebThing\Server;

/**
 * @file
 * High level Action base class implementation.
 */

use WebThing\Thing;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7;

/**
 * Represents an individual action on a thing.
 */
class ThingHandler implements MessageComponentInterface {

  /**
   * List of things managed by this server.
   *
   * @var WebThing\Thing
   */
  protected $things;

  /**
   * List of allowed host names.
   *
   * @var string
   */
  protected $hosts;

  /**
   * Initialize the handler.
   */
  public function __construct($things, $hosts) {
    $this->things = $things;
    $this->hosts = $hosts;
  }

  /**
   * {@inheritdoc}
   */
  public function onOpen(ConnectionInterface $conn) {

    // Set Default Headers

    /*

    TODO: ADD HEADERS =========================
    $conn->httpRequest->withAddedHeader('Access-Control-Allow-Origin', '*');
    $conn->httpRequest->withAddedHeader('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type, Accept');
    $conn->httpRequest->withAddedHeader('Access-Control-Allow-Methods', 'GET, HEAD, PUT, POST, DELETE');

    var_dump($headers);

    */

    $headers = $conn->httpRequest->getHeaders();

    $host = $headers['Host'][0] ?? null;

    if(isset($host) && in_array($host, $this->hosts)) {
      // TODO: REMOVE COMMENT AFTER IMPLEMENTING THE addSubscriber() method
      // $this->thing->addSubscriber()
      return;
    }

    $response = new Response(403);
    $conn->send(Psr7\str($response));
    $conn->close();
  }

  /**
   * {@inheritdoc}
   */
  public function onMessage(ConnectionInterface $from, $msg) {
    $message = json_decode($msg);

    if($message === null && json_last_error() !== JSON_ERROR_NONE && $from !== null) {
      $from->send($this->jsonErrResponse('400 Bad Request', 'Parsing request failed'));

      return;
    }

    if(!isset($message->messageType) && !isset($message->data)) {
      $from->send($this->jsonErrResponse('400 Bad Request', 'Invalid message'));

      return;
    }

    $msgType = $message->messageType;

    if($msgType == 'setProperty') {
    }else if($msgType == 'requestAction') {
    }else if($msgType == 'addEventSubscription') {
    }else{
      $from->send($this->jsonErrResponse('400 Bad Request', 'Unknown messageType: ' . $msgType, true, $message));

      return;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function onClose(ConnectionInterface $conn) {
  }

  /**
   * {@inheritdoc}
   */
  public function onError(ConnectionInterface $conn, \Exception $e) {
    $conn->close();
  }

  /**
   * Add new headers
   */
  public function setHeaders(ConnectionInterface $conn) {

  }

  /**
   * JSON Error message response
   */
  private function jsonErrResponse($statusMsg, $msg, $request = false, $requestMsg = '') {
    $jsonMsg = [
      'messageType' => 'error',
      'data' => [
        'status' => $statusMsg,
        'message' => $msg,
      ],
    ];

    if($request) {
      $jsonMsg['data']['request'] = $requestMsg;
    }

    return json_encode($jsonMsg);
  }
}
