<?php

namespace WebThing;

/**
 * @file
 * The Property class implementation.
 */

use JsonSchema\Validator;
use DeepCopy\DeepCopy;

/**
 * Represents an individual state value of a thing.
 */
class Property implements PropertyInterface {

  /**
   * The Web Thing.
   *
   * @var WebThing\Thing
   */
  protected $thing;

  /**
   * The name of the property.
   *
   * @var string
   */
  protected $name;

  /**
   * Value object to hold the property value.
   *
   * @var WebThing\Value
   */
  protected $value;

  /**
   * Property metadata such as type, description, unit, enum, etc.,
   *
   * @var array|string
   */
  protected $metadata;

  /**
   * The prefix of the URI.
   *
   * @var string
   */
  protected $href_prefix;

  /**
   * The href of the property.
   *
   * @var string
   */
  protected $href;

  /**
   * Initialize the object.
   */
  public function __construct($thing, $name, $value, $metadata = null) {
    $this->thing = $thing;
    $this->name = $name;
    $this->value = $value;
    $this->href_prefix = '';
    $this->href = sprintf("/properties/%s", $this->name);
    $this->metadata = isset($metadata) ? $metadata : [];

    $emitter = $this->value->getEventEmitter();

    $emitter->on('valueUpdate', function($new_value) {
      $this->thing->propertyNotify($this);
    });
  }

  /**
   * {@inheritdoc}
   */
  public function validateValue($value) {
    $validator = new Validator();
    // TODO: CHECK OUT THIS LINE TO RAISE EXCEPTION
    if(isset($this->metadata['readOnly']) && $this->metadata['readOnly'] == true) {
      throw new \Exception("Read-only property '" . $this->name . "'.");
    }

    $vvalue = $value->get();
    $validator->validate($vvalue, $this->metadata);

    if(!$validator->isValid()) {
      throw new \Exception("Invalid property value for '" . $this->name . "'.");
    }
  }

  /**
   * {@inheritdoc}
   */
  public function asPropertyDescription() {
    $copier = new DeepCopy();

    $desc = $copier->copy($this->metadata);

    if(!array_key_exists('links', $desc)) {
      $desc['links'] = [];
    }

    $desc['links'][] = [
      'rel' => 'property',
      'href' => $this->href_prefix . $this->href
    ];

    return $desc;
  }

  /**
   * {@inheritdoc}
   */
  public function setHrefPrefix($prefix) {
    $this->href_prefix = $prefix;
  }

  /**
   * {@inheritdoc}
   */
  public function getHref() {
    return $this->href_prefix;
  }

  /**
   * {@inheritdoc}
   */
  public function getValue() {
    return $this->value->get();
  }

  /**
   * {@inheritdoc}
   */
  public function setValue($value) {
    $this->validateValue($value);
    $this->value->set($value);
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->name;
  }

  /**
   * {@inheritdoc}
   */
  public function getThing() {
    return $this->thing;
  }

  /**
   * {@inheritdoc}
   */
  public function getMetadata() {
    return $this->metadata;
  }
}
