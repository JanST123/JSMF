<?php
/**
  * Created by PhpStorm.
  * User: Jan
  * Date: 16.05.2016
  *
  * Equal Validator
 **/

namespace JSMF\Form\Validator;

class Equal implements \JSMF\Form\Validator\ValidatorInterface {




  public static function validate(\DOMNode $el, $value, $args=null, $formData=null) :bool {
    if (!empty($args[0]) && isset($formData[$args[0]])) {
      return $value == $formData[$args[0]];
    }
    return false;
  }


}