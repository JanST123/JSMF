<?php
/**
  * Created by PhpStorm.
  * User: Jan
  * Date: 16.05.2016
  *
  * Exception class
 **/


namespace JSMF;

class Exception extends \Exception {

  private $_additionalData=[];
  const USER_EXCEPTION = 1337; // will NOT cause a mail sent to dmdev01


  public function __toString() {
    if (DEV_SERVER) return print_r($this, 1);
    return $this->getMessage();
  }


  /**
   * returns additional exception data if set
   * @return Array
   **/
  public function getAdditionalData() :array {
    return $this->_additionalData;
  }

  /**
   * constructs exception
   * @param String|null $message
   * @param Int|0 $code
   * @param Exception|null $previous
   * @param Array $additionalData (append additional data to the exceptoni (makes sense on json response))
   * @return \Exception
   * @throws \Exception
   */
  public function __construct(string $message = null, int $code = 0, \Exception $previous = null, array $additionalData=[]) {
    $this->_additionalData = $additionalData;
    $this->code = $code;
    $this->previous = $previous;
    
    if (DEV_SERVER) {
      $this->message='';
      $trace=$this->getTrace();

      if (isset($trace[0]) && isset($trace[0]['class'])) {
        $this->message.=$trace[0]['class'];
      }
      if (isset($trace[0]) && isset($trace[0]['function'])) {
        if (!empty($this->message)) $this->message .= ', ';
        $this->message.=$trace[0]['function'];
      }
      if (!empty($this->message)) $this->message .= ': ';

      $this->message .= $message;
    } else {
      $this->message = $message;

      if ($this->isBad()) {
        // try to send exception by email
        $mailBody=$this->__toString()
          . "\n" . $this->getTraceAsString()
          . ($previous ? "\n\nPrevious:\n" . $previous->__toString() : '')
          . "\n\nRequest: " . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']
          . "\nReferer: " . (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '-')
          . "\nRequestParams: " . print_r($_REQUEST, 1)
          . "\nUserAgent: " . $_SERVER['HTTP_USER_AGENT']
          . "\nSession Age: " . (\JSMF\Session::isStarted() ? \JSMF\Session::getSessionAge() . ' seconds' : '(not started)')
          . "\nSession Content: " . print_r($_SESSION, 1);

        \JSMF\Email::send(
          defined('WEBMASTER_EMAIL') ? WEBMASTER_EMAIL : 'webmaster@' . (empty($_SERVER['HTTP_HOST']) ? 'localhost' : $_SERVER['HTTP_HOST']),
          'JSMF Exception: ' . $message,
          $mailBody
        );
      }
    }




    return parent::__construct($this->message, (int)$code, $previous);
  }
  
  /**
   * returns if this is a "bad" exception where the developers should be informed about
   * @return Boolean
   **/
  public function isBad() :bool {
    return $this->getCode() != self::USER_EXCEPTION && $this->getCode() != 404;
  }
  
}
