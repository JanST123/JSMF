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

class Redis implements CacheInterface {
  private static $_redis=null;


  /**
   * establishes connection to redis-server if not already exists and alive
   * @return Bool $connected
   **/
  public static function connect() :bool
  {
    if (self::$_redis) {
      // check connection
      try {
        if (self::$_redis->ping() === '+PONG') return true; // redis is there and connection alive
      } catch (\RedisException $e) {
        /* reconnect attempt below */
      }
    }

    self::$_redis = new \Redis();

    $connectionResult = null;
    if (Config::has('cache.redis.socket')) {
      $connectionResult = self::$_redis->connect(Config::get('cache.redis.socket'));

    } elseif (Config::has('cache.redis.host')) {
      $connectionResult = self::$_redis->connect(Config::get('cache.redis.host'), Config::get('cache.redis.port', 6379));
    } else {
      throw new Exception('Neither cache.redis.socket nor cache.redis.host defined in config');
    }

    if ($connectionResult === TRUE) {
      self::$_redis->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP);

      if (Config::has('cache.redis.dbindex')) {
        self::$_redis->select(Config::get('cache.redis.dbindex'));
      }
      return true;
    }

    return false;
  }


  /**
   * switches the active dbindex
   * @param int $index
   **/
  public static function selectDBIndex(int $index) {
    if (!self::connect()) return false;
    self::$_redis->select($index);
  }


  /**
   * returns all redis keys matching the pattern
   * @param String $pattern
   * @return array $keys
   **/
  public static function getKeys(string $pattern='*') :array {
    if (!self::connect()) return false;
    return self::$_redis->keys($pattern);
  }


  /**
   * pops one element of the end of the list
   * @param String key
   * @return mixed
   **/
  public static function listPop(string $key) {
    if (!self::connect()) return false;
    return self::$_redis->rPop($key);
  }


  /**
   * shifts an element from the beginning of a list
   * @param String key
   * @return mixed
   **/
  public static function listShift(string $key) {
    if (!self::connect()) return false;
    return self::$_redis->lPop($key);
  }

  /**
   * pushes an element to the end of a list
   * @param String $key
   * @param mixed $value
   **/
  public static function listPush(string $key, $value) {
    if (!self::connect()) return false;
    return self::$_redis->rPush($key, $value);
  }


  /**
   * inserts an element to the beginning of the list, moving all existing elements on index further
   * @param String $key
   * @param mixed $value
   **/
  public static function listUnshift(string $key, $value) {
    if (!self::connect()) return false;
    return self::$_redis->lPush($key, $value);
  }


  /**
   * returns the number of elements in a list
   * @param String $key
   * @return int $count
   **/
  public static function listCount(string $key) :int {
    if (!self::connect()) return false;
    return self::$_redis->lSize($key);
  }


  public static function get(string $key, $default = null)
  {
    if (!self::connect()) return false;
    
    if (!self::has($key)) return $default;
    return self::$_redis->get($key);
  }

  public static function set(string $key, $value, int $expire = null) :bool
  {
    if (!self::connect()) return false;
    
    return self::$_redis->set($key, $value, $expire);
  }

  public static function has(string $key) :bool
  {
    if (!self::connect()) return false;

    return self::$_redis->exists($key);
  }

  public static function delete(string $key) :bool
  {
    if (!self::connect()) return false;

    if (self::$_redis->delete($key)) return true;
    return false;
  }

}