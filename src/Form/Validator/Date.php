<?php
/**
  * Created by PhpStorm.
  * User: Jan
  * Date: 16.05.2016
  *
  * Date Validator
 **/

namespace JSMF\Form\Validator;

class Date implements \JSMF\Form\Validator\ValidatorInterface {
  public static function validate(\DOMNode $el, $value, $args=null, $formData=null) :bool {

    if (empty($args['format'])) {
      $args['format'] = 'Y-m-d';
    }

    $format = $args['format'];
    $divider=null;
    $dateParts=[];
    for($i=0; $i<strlen($format); ++$i) {
      if (preg_match('/\w/', $format[$i])) $dateParts[]=$format[$i];
      else $divider=$format[$i];
    }

    $parts = explode($divider, $value);
    $day=$month=$year=null;

    foreach ($parts as $i => $part) {
      if (isset($dateParts[$i])) {
        switch($dateParts[$i]) {
          case 'd':
          case 'j':
            $day = $part;
            break;

          case 'm':
          case 'n':
            $month = $part;
            break;

          case 'Y':
            $year = $part;
            break;

          case 'y':
            $year = $part;
            if ($year > 69) $year+=1900;
            else $year+=2000;
            break;
        }
      }
    }

    if ($day && $month && $year) {
      return checkdate($month, $day, $year);
    }

    return false;
  }
  


}