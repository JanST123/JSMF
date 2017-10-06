<?php
/**
  * Created by PhpStorm.
  * User: Jan
  * Date: 16.05.2016
  *
  * NotFoundException class (throws 404 http code and not found error)
 **/

namespace JSMF\Exception;

class NotFound extends \JSMF\Exception {
  public function __construct(string $message=null) {
    return parent::__construct($message ? $message : 'Not Found', 404);
  }
}