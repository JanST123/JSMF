<?php
/**
  * Created by PhpStorm.
  * User: Jan
  * Date: 16.05.2016
  *
  * UserVisible Exception class (exception message is shown to user, no mail is sent)
 **/

namespace JSMF\Exception;


class UserVisible extends \JSMF\Exception {
  private $_headline='Error';

  public function __construct(string $message=null, array $additionalData=[], string $headline=null) {
    parent::__construct($message, parent::USER_EXCEPTION, null, $additionalData);
    $this->_headline=$headline;
  }

  public function getHeadline() {
    return $this->_headline;
  }
}