<?php
/**
  * Created by PhpStorm.
  * User: Jan
  * Date: 16.05.2016
  *
  * Email Validator
 **/

namespace JSMF\Form\Validator;

class Email implements \JSMF\Form\Validator\ValidatorInterface {

  public static function init(\DOMNode $el) {
    if (!$el->hasAttribute('pattern')) {
      $el->addAttribute('pattern', '^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$');
    }
    $el->addAttribute('type', 'email');
  }


  public static function validate(\DOMNode $el, $value, $args=null, $formData=null) :bool {
    if (empty($value) && (String)$value != '0') return true;

    return (bool)filter_var($value, FILTER_VALIDATE_EMAIL);
  }

  public function getHTMLValidatorAttributes($args=null) :array {
    return Array(
      'pattern' => '^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$',
      'title' => Translate::get('validator_fail_msg_Email'),
      'type' => 'email',
    );
    return [];
  }
}