<?php
/**
  * Created by PhpStorm.
  * User: Jan
  * Date: 16.05.2016
  *
  * Required Validator
 **/

namespace JSMF\Form\Validator;

class Required implements \JSMF\Form\Validator\ValidatorInterface {

  public static function init(\DOMNode $el) {
    if (!$el->hasAttribute('required')) {
      $el->addAttribute('required', 'required');
    }
  }

  public static function validate(\DOMNode $el, $value, $args=null, $formData=null) :bool {
    if (is_array($value)) {
      $valid=false;
      if (count($value)) {
        foreach ($value as $v) {
          if (!self::_empty($v)) {
            $valid=true;
            break;
          }
        }
      }
    } else {
      $valid=!self::_empty($value);
    }

    return $valid;
  }
  

  private static function _empty($val) :bool {
    return empty($val) && (String)$val!=='0';
  }

}