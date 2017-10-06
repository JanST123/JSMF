<?php
/**
  * Created by PhpStorm.
  * User: Jan
  * Date: 16.05.2016
  *
  * MinLength Validator
 **/

namespace JSMF\Form\Validator;

use JSMF\Exception;

class MinLength implements \JSMF\Form\Validator\ValidatorInterface {



  public static function validate(\DOMNode $el, $value, $args=null, $formData=null) :bool {

    if (empty($args['minlength']) || (int)$args['minlength'] < 1) {
      throw new Exception('MinLength Validator needs minlength attribute');
    }

    $valid=true;
    if (!self::_empty($value)) {
      if (strlen(trim($value)) < $args['minlength']) {
        $valid=false;
      }
    }

    return $valid;
  }
  

  private static function _empty($val) :bool {
    return empty($val) && (String)$val!=='0';
  }

}