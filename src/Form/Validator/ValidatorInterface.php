<?php
/**
  * Created by PhpStorm.
  * User: Jan
  * Date: 16.05.2016
  *
  * Validator Interface
 **/

namespace JSMF\Form\Validator;

interface ValidatorInterface {
  public static function validate(\DOMNode $el, $value, $args=null, $formData=null) :bool;
}