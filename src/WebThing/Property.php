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
class Property {

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
   * Validate new property value before setting it.
   */
  public function validateValue($value) {
    $validator = new Validator();
    // TODO: CHECK OUT THIS LINE TO RAISE EXCEPTION
    if(isset($this->metadata['readOnly']) && $this->metadata['readOnly'] == true) {
      throw new Exception('Read-only property');
    }

    $vvalue = $this->value->get();
    $validator->validate($vvalue, $this->metadata);

    if(!$validator->isValid()) {
      throw new Exception('Invalid property value');
    }
  }

  /**
   * Get the property description.
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
   * Set the prefix of any hrefs associated with this property.
   */
  public function setHrefPrefix($prefix) {
    $this->href_prefix = $prefix;
  }

  /**
   * Get the href of this property
   */
  public function getHref() {
    return $this->href_prefix;
  }

  /**
   * Get the current property value.
   */
  public function getValue() {
    return $this->value->get();
  }

  /**
   * Set the current value of the property.
   */
  public function setValue($value) {
    $this->validateValue($value);
    $this->value->set($value);
  }

  /**
   * Get the name of this property.
   */
  public function getName() {
    return $this->name;
  }

  /**
   * Get the thing associated with this property.
   */
  public function getThing() {
    return $this->thing;
  }

  /**
   * Get the metadata associated with this property.
   */
  public function getMetadata() {
    return $this->metadata;
  }
}
