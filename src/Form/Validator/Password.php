<?php
/**
  * Created by PhpStorm.
  * User: Jan
  * Date: 16.05.2016
  *
  * Password Validator
 **/

namespace JSMF\Form\Validator;

class Password implements \JSMF\Form\Validator\ValidatorInterface {

  public static function validate(\DOMNode $el, $value, $args=null, $formData=null) :bool {
    $return=[];



    if (!empty($args['minlength'])) {
      if (strlen($value) < $args['minlength']) {
        if (!empty($args['messages']) && !empty($args['messages']['minlength'])) $return[] = $args['messages']['minlength'];
        else return false;
      }
    }
    if (!empty($args['force_digit'])) {
      if (!preg_match('/[[:digit:]]{' . (int)$args['force_digit'] . ',}/', $value )) {
        if (!empty($args['messages']) && !empty($args['messages']['force_digit'])) $return[] = $args['messages']['force_digit'];
        else return false;
      }
    }
    if (!empty($args['force_alpha'])) {
      if (!preg_match('/[[:alpha:]]{' . (int)$args['force_alpha'] . ',}/', $value )) {
        if (!empty($args['messages']) && !empty($args['messages']['force_alpha'])) $return[] = $args['messages']['force_alpha'];
        else return false;
      }
    }
    if (in_array('no_blanks', $args)) {
      if (preg_match('/[[:blank:]]/', $value )) {
        if (!empty($args['messages']) && !empty($args['messages']['no_blanks'])) $return[] = $args['messages']['no_blanks'];
        else return false;
      }
    }
    

    if (!empty($return)) return $return;
    return true;
  }

}