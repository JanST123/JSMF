<?php
/**
  * Created by PhpStorm.
  * User: Jan
  * Date: 16.05.2016
  *
  * Value Validator (requires a specific value)
 **/

namespace JSMF\Form\Validator;

use JSMF\Exception;

class Value implements \JSMF\Form\Validator\ValidatorInterface {



  public static function validate(\DOMNode $el, $value, $args=null, $formData=null) :bool {

    if (!isset($args['value'])) {
      throw new Exception('Value Validator needs value attribute');
    }

    if (!empty($args['convert'])) {
      switch($args['convert']) {
        case 'md5':
          $value = md5($value);
          break;
      }
    }

    return $value == $args['value'];
  }

}