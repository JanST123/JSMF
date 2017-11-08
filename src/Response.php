<?php
/**
  * Created by PhpStorm.
  * User: Jan
  * Date: 16.05.2016
  *
  * Response Class
 **/
namespace JSMF;

use JSMF\Exception\UserVisible;

class Response {
  private static $_headers=Array();
  private static $_output=null;
  private static $_outputFormat='html';


  public static function init() {
    self::addHeader('Cache-Control: no-cache, no-store, must-revalidate, private');
  }


  /**
   * adds a header for sending later
   * @param String $header
   * @param Int|null $httpcode
   * @param Boolean|true $replace (meaning of replace see: http://php.net/manual/en/function.header.php)
   * @param Boolean|false $onlyIfNotExists (add the header only if no other header of this type (e.g. Content-Type) exists
   * @return Void
   */
  public static function addHeader(string $header, int $httcode=null, bool $replace=true, bool $onlyIfNotExists=false) {
    $headerKey = explode(':', $header)[0];

    if ($onlyIfNotExists && isset(self::$_headers[$headerKey])) return;

    if (!isset(self::$_headers[$headerKey]) || $replace) {
      self::$_headers[$headerKey]=Array();
    }
    self::$_headers[$headerKey][]=Array($httcode, $replace, $header);
  }


  /**
   * set an output format (default is json)
   * @param String $format
   * @return Void
   **/
  public static function setOutputFormat(string $format) {
    $formatsAllowed=Array('html', 'json');
    if (in_array($format, $formatsAllowed)) {
      self::$_outputFormat=$format;  
    } else {
      throw new Exception('Invalid output format. Valid formats are: '.implode(', ', $formatsAllowed));
    }
  }
  
 
  /**
   * adds a 404 not found header
   * @return Void
   */
  public static function addHeaderNotFound() {
    return self::addHeader('HTTP/1.0 404 Not Found', 404);
  }

  /**
   * adds a 500 internal server error header
   * @return Void
   */
  public static function addHeaderServerError() {
    return self::addHeader('HTTP/1.0 500 Internal Server Error', 500);
  }


  /**
   * sets the output for sending later. JSON Encodes non-scalar values
   * @param Mixed $data
   * @return Void
   */
  public static function setOutput($data) {
    if ($data===null) {
      self::addHeader('Content-Type: text/html; charset=UTF-8', null, true, true);
      self::$_output='';
      
    } elseif (self::$_outputFormat=='json' || (!is_scalar($data) && !$data instanceof Template)) {
      Response::addHeader('Content-Type: application/json; charset=UTF-8', null, true, true);
      
      //if (is_array($data) && !isset($data['success'])) $data['success']=true;
      $options=null;
      if (Request::get('json_pretty_print', false)) {
        $options=JSON_PRETTY_PRINT;
      }
      self::$_output=json_encode($data, $options);
        
    } else {
      self::addHeader('Content-Type: text/html; charset=UTF-8', null, true, true);

      // disable layout on ajax requests
      if ($data instanceof Template && Request::isAjax()) $data->disableLayout();
      
      self::$_output=(String)$data;
    }
  }


  /**
   * sets exception for outputting
   * @param \JSMF\Exception $e
   * @return Void
   */
  public static function setException(\JSMF\Exception $e) {
    if ($e->isBad()) {
      self::addHeaderServerError();
    }

    if ($e->getCode() == 404) {
      self::addHeaderNotFound();
    }

    $output='';

    if ($e instanceof UserVisible) {
      if (self::$_outputFormat == 'json' || Request::expectsJson()) {
        $output = Array(
          'success' => false,
          'message' => $e->getMessage(),
          'headline' => $e->getHeadline(),
        );
      } else {
        $output=<<<HTML
<h2>{$e->getHeadline()}</h2>
<p>{$e->getMessage()}</p>
HTML;

      }

    } else {
      $message = DEV_SERVER || $e->getCode() == Exception::USER_EXCEPTION ? $e->getMessage() : \JSMF\Language::get('exception_default');

      if (self::$_outputFormat == 'json' || Request::expectsJson()) {
        $output = Array(
          'success' => false,
          'message' => $message,
          'exception' => DEV_SERVER ? [
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTrace()
          ] : ['message' => $e->getMessage(), ],
        );
        if ($e instanceof \JSMF\Exception) {
          $output = array_merge($e->getAdditionalData(), $output);
        }

      } else {
        $output = '<h3>' . $message . '</h3>';
        if (DEV_SERVER) {
          $output .= '<pre style="border: 1px dashed red; color: red;">' . (String)$e . '</pre>';
        }
      }
    }

    if ($output) self::setOutput($output);
  }


  /**
   * sends the headers and output to the browser
   * @return Void
   */
  public static function output() {
    // send headers
    foreach (self::$_headers as $headerKey => $headers) {
      foreach ($headers as $headerData) {
        header($headerData[2], $headerData[1], $headerData[0]);
      }
    }

    echo self::$_output;
  }

}
