<?php
/**
 * Created by PhpStorm.
 * User: jan
 * Date: 31.01.17
 * Time: 19:10
 */

namespace JSMF\Cache;
use JSMF\Config;
use JSMF\Exception;

class Memcache implements CacheInterface {
  private static $_mc=null;


  /**
   * connects to memcache server if not already connected, returns status
   * @return Boolean $success
   **/
  public static function connect() :bool {
    if (self::$_mc) return true;

    if (Config::has('cache.memcache.host')) {
      self::$_mc=new \Memcache;
      self::$_mc->connect(Config::get('cache.memcache.host'), Config::get('cache.memcache.port', 11211));
      return true;

    } else {
      throw new Exception('cache.memcache.host not defined in config');
    }

    return false;
  }





  /**
   * return item from memcache
   * @param String $key
   * @param Mixed $default
   * @return Mixed $value
   **/
  public static function get(string $key, $default=null) {
    if (!self::connect()) return false;

    $val = self::$_mc->get($key);
    if ($val !== FALSE) { return json_decode($val, true); }
    return $default;
  }


  /**
   * stores value in memcache
   * @param String $key
   * @param Mixed $value
   * @param Int|null $expire
   * @return Boolean $success
   **/
  public static function set(string $key, $value, int $expire=null) :bool {
    if (!self::connect()) return false;

    return self::$_mc->set($key, json_encode($value), null, $expire);
  }


  /**
   * checks if memcache key exists
   * @param String $key
   * @return Boolean $exists
   **/
  public static function has(string $key) :bool {
    if (!self::connect()) return false;

    if (-1 == self::get($key, -1)) return false;
    return true;
  }


  /**
   * deletes item from memcache
   * @param String $key
   * @return Boolean $success
   **/
  public static function delete(string $key) :bool {
    if (!self::connect()) return false;
    return self::$_mc->delete($key);
  }


}