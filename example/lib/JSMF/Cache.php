<?php
/**
  * Created by PhpStorm.
  * User: Jan
  * Date: 16.05.2016
  *
  * Memcache controller
 **/

namespace JSMF;

class Cache implements Cache\CacheInterface {


  private static function _getCacheBackend() {
    if (!Config::has('cache.backend')) {
      throw new Exception('cache.backend not defined in Config.');
    }
    $cacheBackend = Config::get('cache.backend');


    $cls='\\JSMF\\Cache\\' . ucfirst($cacheBackend);
    if (class_exists($cls)) {
      return $cls;
    }
    throw new Exception('Unknown Cache backend');
  }


  /**
   * connects to server if not already connected, returns status
   * @return Boolean $success
   **/
  public static function connect() :bool {
    return call_user_func(self::_getCacheBackend() . '::connect');
  }


  /**
   * return item from cache
   * @param String $key
   * @param Mixed $default
   * @return Mixed $value
   **/
  public static function get(string $key, $default=null) {
    return call_user_func(self::_getCacheBackend() . '::get', $key, $default);
  }


  /**
   * stores value in cache
   * @param String $key
   * @param Mixed $value
   * @param Int|null $expire
   * @return Boolean $success
   **/
  public static function set(string $key, $value, int $expire=null) :bool {
    return call_user_func(self::_getCacheBackend() . '::set', $key, $value, $expire);
  }


  /**
   * checks if cache key exists
   * @param String $key
   * @return Boolean $exists
   **/
  public static function has(string $key) :bool {
    return call_user_func(self::_getCacheBackend() . '::has', $key);
  }


  /**
   * deletes item from cache
   * @param String $key
   * @return Boolean $success
   **/
  public static function delete(string $key) :bool {
    return call_user_func(self::_getCacheBackend() . '::delete', $key);
  }

}