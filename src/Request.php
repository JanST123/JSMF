<?php
/**
  * Created by PhpStorm.
  * User: Jan
  * Date: 16.05.2016
  *
  * Request class!!
 **/
namespace JSMF;

use JSMF\Response;

/**
 * Handle and analyse the current HTTP Request
 **/
class Request {
  private static $_instance;
  private static $_region=null;
  private static $_subpage=null;
  private static $_routes=Array();
  private static $_routeParams=Array();
  private static $_unserializedPayload=null;


  /**
   * Returns instance of this class
   * @return Request
   */
  public static function getInstance() :\JSMF\Request {
    if (!self::$_instance) {
      self::$_instance=new self();
    }
    return self::$_instance;
  }

  /**
   * returns if current request is ajax request
   * @return Boolean $ajax
   */
  public static function isAjax() :bool {
    if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' || (isset($_REQUEST['ajax']) && $_REQUEST['ajax']==1)) {
      return true;
    }
    return false;
  }


  /**
   * returns if current request expects JSON as answert
   * @return bool
   */
  public static function expectsJson() :bool {
    if (!empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') === 0) {
      return true;
    }
    return false;
  }

  /**
   * alias got get method
   * @param String $key
   * @return Mixed $value
   */
  public function __get($key) {
    return self::get($key);
  }
  /**
   * sets a request var (you normally should not use this...)
   * @param String $key
   * @param Mixed $value
   * @return Void
   */
  public function __set($key, $value) {
    $_REQUEST[$key]=$value;
    $_GET[$key]=$value;
    $_POST[$key]=$value;
  }


  /**
   * serialize the current request for saving
   * @return string
   */
  public static function serialize() :string {
    $data=[
      'request' => $_REQUEST,
      'get' => $_GET,
      'post' => $_POST,
      'payload' => self::getPayload(),
    ];

    return serialize($data);
  }


  /**
   * Unserialize request data
   * @param string $dataStr
   */
  public static function unserialize(string $dataStr) {
    $data = unserialize($dataStr);

    if (!empty($data['request'])) $_REQUEST = $data['request'];
    if (!empty($data['get'])) $_GET = $data['get'];
    if (!empty($data['post'])) $_POST = $data['post'];
    if (!empty($data['payload'])) self::$_unserializedPayload = $data['payload'];
  }


  /**
   * gets request param, with default value, optionally sanitize
   * @param String $key
   * @param String|null $default
   * @param String|REQUEST $from (GET, POST, PAYLOAD or REQUEST)
   * @param Boolean|true $sanitized
   * @return Mixed $value
   */
  public static function get(string $key, string $default=null, string $from='REQUEST', bool $sanitized=true) {
    $key=strtolower($key);
    $data=null;
    $payload=Array();
    if (in_array($_SERVER['REQUEST_METHOD'], array('POST', 'PUT'))) {
      $payload=self::getPayload(false, true);
      if (!is_array($payload)) $payload=array();
    }

    switch($from) {
      case 'REQUEST': $data=array_merge($_REQUEST, self::$_routeParams, $payload); break;
      case 'GET': $data=$_GET; break;
      case 'POST': $data=$_POST; break;
      case 'COOKIE': $data=$_COOKIE; break;
      case 'PAYLOAD': $data=$payload; break;
    }

    // wrong $from parameter - return default
    if (!$data) return $default;

    // lower data keys
    $data=array_change_key_case($data, CASE_LOWER);

    // param does not exist - return default
    if (!isset($data[$key])) return $default;

    if ($sanitized) {
      if (is_array($data[$key])) {
        array_walk_recursive($data[$key], function(&$val, $index) {
          if (is_scalar($val)) {
            $val=filter_var($val, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
          }
        });
        return $data[$key];
      }
      return filter_var($data[$key], FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
    }
    return $data[$key];
  }


  /**
   * returns if param exists in request
   * @param String $key
   * @param String|REQUEST $from
   * @return boolean
   **/
  public static function has(string $key, string $from='REQUEST') :bool {
    $key=strtolower($key);
    $data=null;
    $payload=Array();
    if (in_array($_SERVER['REQUEST_METHOD'], array('POST', 'PUT'))) {
      $payload=self::getPayload(false, true);
      if (!is_array($payload)) $payload=array();
    }
    switch($from) {
      case 'REQUEST': $data=array_merge($_REQUEST, self::$_routeParams, $payload); break;
      case 'GET': $data=$_GET; break;
      case 'POST': $data=$_POST; break;
      case 'COOKIE': $data=$_COOKIE; break;
      case 'PAYLOAD': $data=$payload; break;
    }

    // lower data keys
    $data=array_change_key_case($data, CASE_LOWER);
    
    return isset($data[$key]);
  }

  /**
   * alias for get(..., ..., 'GET', ...)
   * @param String $key
   * @param String|null $default
   * @param Boolean|true $sanitized
   * @return Mixed $value
   */
  public static function getGet(string $key, string $default=null, bool $sanitized=true) {
    return self::get($key, $default, 'GET', $sanitized);
  }

  /**
   * alias for get(..., ..., 'POST', ...)
   * @param String $key
   * @param String|null $default
   * @param Boolean|true $sanitized
   * @return Mixed $value
   */
  public static function getPost(string $key, string $default=null, bool $sanitized=true) {
    return self::get($key, $default, 'POST', $sanitized);
  }


  /**
   * alias for get(..., ..., 'COOKIE', ...)
   * @param String $key
   * @param String|null $default
   * @param Boolean|true $sanitized
   * @return Mixed $value
   */
  public static function getCookie(string $key, string $default=null, bool $sanitized=true) {
    return self::get($key, $default, 'COOKIE', $sanitized);
  }


  /**
   * returns if request_method is POST
   * @return Boolean $isPost
   **/
  public static function isPost() :bool {
    return isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD']=='POST';
  }


  /**
   * returns payload
   * @param Boolean|true $raw
   * @param Boolean|false $forceAssociativeArray
   * @return Mixed $data
   */
  public static function getPayload(bool $raw=true, bool $forceAssociativeArray=false) {
    $payload=self::$_unserializedPayload !== null ? self::$_unserializedPayload : file_get_contents('php://input');
    if ($raw) {
      return $payload;
    } else {
      // try to parse json
      @$data=json_decode($payload, $forceAssociativeArray);
      return $data;
    }
  }
  

  /**
   * returns the origin sent by parameter or header or null if no origin given
   * @return String
   **/
  public static function getOrigin() :string {
    return self::get('origin', (isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:null));
  }


  /**
   * redirect to the location, this will send the header directly and php script is aborted
   * @param String $location
   * @param Boolean|false $permanent
   * return Void
   */
  public static function redirect(string $location, bool $permanent=false) {
    $code=$permanent?301:302;
    if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD']=='POST') {
      $code=307;
    }

    Response::addHeader('Location: '.$location, $code, true);
  }
  

  /**
   * adds a new route which (url pattern which will redirect to targetcontroller, targetaction with target parameters)
   * @param String $regex
   * @param String $targetModule
   * @param String|index $targetController
   * @param String|index $targetAction
   * @param Array|[] $targetParameters
   */
  public static function addRoute(string $regex, string $targetModule, string $targetController='index', string $targetAction='index', array $targetParameters=[]) {
    self::$_routes[$regex]=Array(
      'module' => $targetModule,
      'controller' => $targetController,
      'action' => $targetAction,
      'params' => $targetParameters
    );
  }


  /**
   * matches the routes to the url. if route matches returns the route with controller and action. PArams will be set
   * If no route matches returns false
   * @return Array/Boolean
   */
  private static function _matchRoutes() {
    foreach (self::$_routes as $regex => $route) {
      $matches=Array();

      if (preg_match($regex, urldecode(self::getRequestUri()), $matches)) {
        self::$_routeParams=Array();
        foreach ($route['params'] as $key => $val) {
          if (strpos($val, '$')===0) {
            self::$_routeParams[$key]=isset($matches[substr($val, 1)])?$matches[substr($val, 1)]:null;
          } else {
            self::$_routeParams[$key]=$val;
          }
        }
        return $route;
      }
    }
    return false;
  }





  /**
   * gets the controller or returns default if not given
   * url format /module/controller/action
   * @param String $default
   * @return String
   */
  public static function getController(string $default) :string {
    $controllerOverride=self::get('controller');
    if ($controllerOverride) return $controllerOverride;

    $route=self::_matchRoutes();
    if ($route) {
      return $route['controller'];
    }

    if (preg_match('/\/([a-z0-9\-_öäüß]+)\/([a-z0-9\-_öäüß]+)([\/\?](.*))?$/i', urldecode(self::getRequestUri()), $matches)) {
      return ucfirst(self::_dashToCamelCase($matches[2]));
    }
    
    return $default;
  }


  /**
   * gets the action or returns default if not given
   * url format /module/controller/action
   * @param String $default
   * @return String
   */
  public static function getAction(string $default) :string {
    $actionOverride=self::get('action');
    if ($actionOverride) return $actionOverride;
    
    $route=self::_matchRoutes();
    if ($route) {
      return $route['action'];
    }

    if (preg_match('/\/([a-z0-9\-_öäüß]+)\/([a-z0-9\-_öäüß]+)\/([a-z0-9\-_öäüß]+)([\/\?](.*))?$/i', urldecode(self::getRequestUri()), $matches)) {
      return self::_dashToCamelCase($matches[3]);
    }

    return $default;
  }



  /**
   * gets the module or returns default if not given
   * url format /module/controller/action
   * @param String $default
   * @return String
   */
  public static function getModule(string $default) :string {
    $moduleOverride=self::get('module');
    if ($moduleOverride) return $moduleOverride;

    $route=self::_matchRoutes();
    if ($route) {
      return $route['module'];
    }

    if (preg_match('/\/([a-z0-9\-_öäüß]+)([\/\?](.*))?$/i', urldecode(self::getRequestUri()), $matches)) {
      return ucfirst(self::_dashToCamelCase($matches[1]));
    }

    return $default;
  }


  /**
   * convert dashed-names to camelCase names
   * @param String $str
   * @return String
   */
  private static function _dashToCamelCase(string $str) :string {
    return preg_replace_callback('/\-[a-z0-9]/i', function($matches) { return strtoupper(substr($matches[0], 1)); }, $str);
  }


  /**
   * returns if current request is cross origin request
   * @return bool $isCrossOrigin
   */
  public static function isCrossOrigin() :bool {
    $host = 'http' . (!empty($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'];
    if (!empty($_SERVER['HTTP_ORIGIN'])
      && $_SERVER['HTTP_ORIGIN'] != $host) {
      return true;
    }

    return false;
  }


  /**
   * returns the uri parts without module/controller/action as an array
   * @return Array
   */
  public static function getUriParts() :array {
    $offset=1;
    if (self::getModule('.') !== '.') ++$offset;
    if (self::getController('.') !== '.') ++$offset;
    if (self::getAction('.') !== '.') ++$offset;

    $expl=explode('/', self::getRequestUri());

    if ($offset >= count($expl)) return [];
    return array_slice($expl, $offset);
  }
  
  
  /**
   * returns the request array, attends to proxy forwareded uri
   * @return String
   **/
  public static function getRequestUri() :string {
    $uri = (!empty($_SERVER['HTTP_X_FORWARDED_QUERYSTRING'])) ? $_SERVER['HTTP_X_FORWARDED_QUERYSTRING'] : $_SERVER['REQUEST_URI'];

    return explode('?' , $uri)[0];
  }



}
  
