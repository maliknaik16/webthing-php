<?php

namespace WebThing;

require '../../vendor/autoload.php';
/**
 * @file
 * The Thing class implementation.
 */

use WebThing\Server\WebSocketHandler;
use JsonSchema\Validator;

/**
 * Represents an Web Thing.
 */
class Thing implements ThingInterface {

  /**
   * The Web Thing.
   *
   * @var array
   */
  protected $thing;

  /**
   * Provides a URI for a schema repository.
   *
   * @var string
   */
  protected $context;

  /**
   * The thing's type.
   *
   * @var array|string
   */
  protected $type;

  /**
   * The thing's unique id - must be a URI.
   *
   * @var string
   */
  protected $id;

  /**
   * The thing's title.
   *
   * @var string
   */
  protected $title;

  /**
   * The description of the thing.
   *
   * @var string
   */
  protected $description;

  /**
   * The properties/attributes of the thing.
   *
   * @var WebThing\Property[]
   */
  protected $properties;

  /**
   * List of available actions.
   *
   * @var array
   */
  protected $available_actions;

  /**
   * List of available events.
   *
   * @var array
   */
  protected $available_events;

  /**
   * The actions.
   *
   * @var array
   */
  protected $actions;

  /**
   * The events.
   *
   * @var array
   */
  protected $events;

  /**
   * The subscribers.
   *
   * @var \SplObjectStorage
   */
  protected $subscribers;

  /**
   * The scheme of the URL.
   *
   * @var string
   */
  protected $href_prefix;

  /**
   * The scheme for the URL with media type like HTML.
   *
   * @var string
   */
  protected $ui_href;

  /**
   * The WebSocket handler class.
   */
  protected $webSocketHandler;

  /**
   * Initialize the Thing.
   */
  public function __construct($id, $title, $type = [], $description = '') {
    $this->id = $id;
    $this->type = $type;
    $this->title = $title;
    $this->description = $description;
    $this->context = 'https://iot.mozilla.org/schemas';
    $this->properties = [];
    $this->available_actions = [];
    $this->available_events = [];
    $this->actions = [];
    $this->events = [];
    $this->subscribers = new \SplObjectStorage;
    $this->href_prefix = '';
    $this->ui_href = null;

    $this->webSocketHandler = new WebSocketHandler;
  }

  /**
   * {@inheritdoc}
   */
  public function asThingDescription() {
    $this->thing = [
      'id' => $this->id,
      '@context' => $this->context,
      'title' => $this->title,
      'properties' => $this->getPropertyDescriptions(),
      'actions' => [],
      'events' => [],
      'links' => [
        [
          'rel' => 'properties',
          'href' => sprintf("%s/properties", $this->href_prefix),
        ],
        [
          'rel' => 'actions',
          'href' => sprintf("%s/actions", $this->href_prefix),
        ],
        [
          'rel' => 'events',
          'href' => sprintf("%s/events", $this->href_prefix),
        ],
      ],
    ];

    foreach($this->available_actions as $name => $action) {
      $this->thing['actions'][$name] = $action['metadata'];
      $this->thing['actions'][$name]['links'] = [
        'rel' => 'action',
        'href' => sprintf("%s/actions/%s", $this->href_prefix, $name),
      ];
    }

    foreach($this->available_events as $name => $event) {
      $this->thing['events'][$name] = $event['metadata'];
      $this->thing['events'][$name]['links'] = [
        'rel' => 'event',
        'href' => sprintf("%s/events/%s", $this->href_prefix, $name),
      ];
    }

    if($this->ui_href != null) {
      $this->thing['links'][] = [
        'rel' => 'alternate',
        'mediaType' => 'text/html',
        'href' => $this->ui_href,
      ];
    }

    if(empty($this->description)) {
      $this->thing['description'] = $this->description;
    }

    if(!empty($this->type)) {
      $this->thing['@type'] = $this->type;
    }

    return $this->thing;
  }

  /**
   * {@inheritdoc}
   */
  public function getPropertyDescriptions() {
    $property_descriptions = [];

    foreach($this->properties as $k => $v) {
      $property_descriptions[] = [
        $k => $v->asPropertyDescription(),
      ];
    }

    return $property_descriptions;
  }

  /**
   * {@inheritdoc}
   */
  public function getHref() {
    if(empty($this->href_prefix)) {
      return '/';
    }

    return $this->href_prefix;
  }

  /**
   * {@inheritdoc}
   */
  public function getUiHref() {
    return $this->ui_href;
  }

  /**
   * {@inheritdoc}
   */
  public function propertyNotify(Property $property) {
    $message = json_encode([
      'messageType' => 'propertyStatus',
      'data' => [
        $property->getName() => $property->getValue()
      ]
    ]);

    // TODO: SEND TO THE WEBSOCKET
    //$this->webSocketHandler->notifyChange($message);

    foreach($this->subscribers as $subscriber) {
      $subscriber->send($message);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function setHrefPrefix($prefix) {
    $this->href_prefix = $prefix;

    foreach($this->properties as $property) {
      $property->setHrefPrefix($prefix);
    }

    // TODO: CONFIRM THIS IMPLEMENTATION
    foreach($this->actions as $action_name => $action) {
      $action->setHrefPrefix($prefix);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function setUiHref($href) {
    $this->ui_href = $href;
  }

  /**
   * {@inheritdoc}
   */
  public function getId() {
    return $this->id;
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return $this->title;
  }

  /**
   * {@inheritdoc}
   */
  public function getContext() {
    return $this->context;
  }

  /**
   * {@inheritdoc}
   */
  public function getType() {
    return $this->type;
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->description;
  }

  /**
   * {@inheritdoc}
   */
  public function getActionDescriptions($action_name = '') {
    $descriptions = [];

    if(empty($action_name)) {
      // TODO: Figure out the action structure
      foreach($this->actions as $name => $action) {
        $descriptions[] = $action->asActionDescription();
      }
    }else if(in_array($action_name, $this->actions)) {
    }
    return $descriptions;
  }

  /**
   * {@inheritdoc}
   */
  public function getEventDescriptions($event_name = '') {
    $descriptions = [];
    if(empty($event_name)) {
      foreach($this->events as $event) {
        $descriptions[] = $event->asEventDescription();
      }
    }else{
      foreach($this->events as $event) {
        if($event->getName() == $event_name) {
          $descriptions[] = $event->asEventDescription();
        }
      }
    }

    return $descriptions;
  }

  /**
   * {@inheritdoc}
   */
  public function addProperty(Property $property) {
    $property->setHrefPrefix($this->href_prefix);
    $this->properties[$property->name] = $property;
  }

  /**
   * {@inheritdoc}
   */
  public function removeProperty(Property $property) {
    if(in_array($property->name, $this->properties)) {
      unset($this->properties[$property->name]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function findProperty($property_name) {
    if(isset($this->properties[$property_name])) {
      return $this->properties[$property_name];
    }

    return null;
  }

  /**
   * {@inheritdoc}
   */
  public function getProperty($property_name) {
    $property = $this->findProperty($property_name);

    if(!is_null($property)) {
      return $property->getValue();
    }

    return null;
  }

  /**
   * {@inheritdoc}
   */
  public function getProperties() {

    // TODO: RECHECK THE FOREACH LOOP
    $properties = [];

    foreach($this->properties as $property) {
      $properties[] = [$property->getName() => $property->getValue()];
    }

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function hasProperty($property_name) {
    return in_array($property_name, $this->properties);
  }

  /**
   * {@inheritdoc}
   */
  public function setProperty($property_name, $value) {
    $property = $this->findProperty($property_name);

    if(!$property) {
      return;
    }

    $property->setValue($value);
  }

  /**
   * {@inheritdoc}
   */
  public function getAction($action_name, $action_id) {
    if(!in_array($action_name, $this->actions)) {
      return null;
    }

    foreach($this->actions[$action_name] as $action) {
      if($action->id == $action_id) {
        return $action;
      }
    }

    return null;
  }

  /**
   * {@inheritdoc}
   */
  public function addEvent(Event $event) {
    $this->events[] = $event;
    $this->eventNotify($event);
  }

  /**
   * {@inheritdoc}
   */
  public function addAvailableEvent($name, $metadata) {
    if(!$metadata) {
      $metadata = [];
    }

    $this->available_events[$name] = [
      'metadata' => $metadata,
      'subscribers' => [],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function performAction($action_name, $input = null) {

    // TODO: Re check this class again.
    if(!in_array($action_name, $this->available_actions)) {
      return null;
    }

    $action_type = $this->available_actions[$action_name];

    if(in_array('input', $action_type['metadata'])) {
      $validator = new Validator();
      $validator->validate($input, $action_type['metadata']['input']);

      if(!$validator->isValid()) {
        return null;
      }
    }

    $action = $action_type['class']($input);
    $action->setHrefPrefix($this->href_prefix);
    $this->notifyAction($action);
    $this->action[$action_name] = $action;

    return $action;
  }

  /**
   * {@inheritdoc}
   */
  public function removeAction($action_name, $action_id) {
    $action = $this->getAction($action_name, $action_id);

    if(!$action) {
      return false;
    }

    $action->cancel();
    // TODO: Find the solution to remove specific element from the array.

    if(($key = array_search($action, $this->actions[$action_name], true)) !== FALSE) {
      unset($this->actions[$action_name][$key]);
      return true;
    }
    return false;
  }

  /**
   * {@inheritdoc}
   */
  public function addAvailableAction($name, $metadata, $cls) {
    if(!$metadata) {
      $metadata = [];
    }

    $this->available_actions[$name] = [
      'metadata' => $metadata,
      'class' => $cls,
    ];

    $this->actions[$name] = [];
  }

  /**
   * {@inheritdoc}
   */
  public function addSubscriber($ws) {
    $this->subscribers->add($ws);
  }

  /**
   * {@inheritdoc}
   */
  public function removeSubscriber($ws) {
    if($this->subscribers->contains($ws)) {
      $this->subscribers->detach($ws);
    }

    foreach($this->available_events as $name) {
      $this->removeEventSubscriber($name, $ws);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function addEventSubscriber($name, $ws) {
    if(in_array($name, $this->available_events)) {
      $this->available_events[$name]['subscribers'][] = $ws;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function removeEventSubscriber($name, $ws) {
    if(in_array($name, $this->available_events) && ($key = array_search($ws, $this->available_events[$name]['subscribers'])) !== FALSE) {
      unset($this->available_events[$name]['subscribers'][$key]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function actionNotify(Action $action) {
    $message = json_encode([
      'messageType' => 'actionStatus',
      'data' => $action->asActionDescription(),
    ]);

    // TODO: RECHECK THE FOLLOWING
    foreach($this->subscribers as $subscriber) {
      $subscriber->send($message);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function eventNotify(Event $event) {
    if(!in_array($event, $this->available_events)) {
      return;
    }

    $message = json_encode([
      'messageType' => 'event',
      'data' => $event->asEventDescription(),
    ]);

    foreach($this->available_events[$event->getName()]['subscribers'] as $subscriber) {
      $subscriber->send($message);
    }
  }
}
