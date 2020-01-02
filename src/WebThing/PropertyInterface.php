<?php

namespace WebThing;

interface PropertyInterface {

  /**
   * Validate new property value before setting it.
   */
  public function validateValue($value);

  /**
   * Get the property description.
   */
  public function asPropertyDescription();

  /**
   * Set the prefix of any hrefs associated with this property.
   */
  public function setHrefPrefix($prefix);

  /**
   * Get the href of this property.
   */
  public function getHref();

  /**
   * Get the current property value.
   */
  public function getValue();

  /**
   * Set the current value of the property.
   */
  public function setValue($value);

  /**
   * Get the name of this property.
   */
  public function getName();

  /**
   * Get the thing associated with this property.
   */
  public function getThing();

  /**
   * Get the metadata associated with this property.
   */
  public function getMetadata();

}
