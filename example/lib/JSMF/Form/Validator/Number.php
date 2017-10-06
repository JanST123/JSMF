<?php
/**
  * Created by PhpStorm.
  * User: Jan
  * Date: 16.05.2016
  *
  * Numeric Validator
 **/

namespace JSMF\Form\Validator;

class Number implements \JSMF\Form\Validator\ValidatorInterface {

  public static function init(\DOMNode $el) {
    if (!$el->hasAttribute('pattern')) {
      $el->addAttribute('pattern', '[0-9]+');
    }
  }

  public static function validate(\DOMNode $el, $value, $args=null, $formData=null) :bool {
    return (int)$value == $value;
  }
  



}