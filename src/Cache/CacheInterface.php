<?php
namespace JSMF\Cache;

interface CacheInterface {
  /**
   * connects to server if not already connected, returns status
   * @return Boolean $success
   **/
  public static function connect() :bool;


  /**
   * return item from cache
   * @param String $key
   * @param Mixed $default
   * @return Mixed $value
   **/
  public static function get(string $key, $default=null);


  /**
   * stores value in cache
   * @param String $key
   * @param Mixed $value
   * @param Int|null $expire
   * @return Boolean $success
   **/
  public static function set(string $key, $value, int $expire=null) :bool;


  /**
   * checks if cache key exists
   * @param String $key
   * @return Boolean $exists
   **/
  public static function has(string $key) :bool;


  /**
   * deletes item from cache
   * @param String $key
   * @return Boolean $success
   **/
  public static function delete(string $key) :bool;
}