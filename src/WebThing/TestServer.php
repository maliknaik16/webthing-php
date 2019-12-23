<?php

namespace WebThing;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class TestServer implements MessageComponentInterface {
  protected $clients;

  public function __construct() {
    $this->clients = new \SplObjectStorage;
  }

  public function onOpen(ConnectionInterface $conn) {
    $this->clients->attach($conn);
  }

  public function onMessage(ConnectionInterface $from, $msg) {
    foreach($this->clients as $client) {
      if($from !== $client) {
        $client->send("Testing: " . $msg);
      }
    }
  }

  public function onClose(ConnectionInterface $conn) {
    $this->clients->detach($conn);
  }

  public function onError(ConnectionInterface $conn, \Exception $e) {
    $conn->close();
  }
}
