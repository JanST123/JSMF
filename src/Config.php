<?php
/**
  * Created by PhpStorm.
  * User: Jan
  * Date: 16.05.2016
  *
  * Simple Config Registry
 **/
namespace JSMF;

class Config {
  private static $_config = [];

  /**
   * load config file. Multiple config files were merged to one config registry
   * @param String $configFile (absolute path)
   * @return Void
   * @throws Exception
   **/
  public static function load(string $configFile) {
    if (!file_exists($configFile)) throw new Exception('Config File does not exist: ' . $configFile);
//    if (!strstr(exec(PHP_BINDIR . '/php -l ' . escapeshellarg($configFile) . ' 2>&1', $output), 'No syntax errors detected')) throw new Exception('Config File contains errors: ' . $configFile . ' ' . implode(" ", $output));

    include ($configFile);
    if (!isset($config) || !is_array($config)) throw new Exception('Config File does not set $config Variable or $config is not an array');

    self::$_config = array_merge(self::$_config, $config);
  }


  /**
   * returns pointer to the right config key from dotted notation config key
   * @return Pointer
   **/
  private static function _getConfigPointer(string $configKey, &$exists=null) {
    $expl = explode('.', $configKey);
    $pointer=self::$_config;
    

    foreach ($expl as $p) {
      if (isset($pointer[$p])) {
        $pointer=&$pointer[$p];
        $exists=true;
      } else {
        $pointer=null;
        $exists=false;
        break;
      }
    }
    return $pointer;
  }

  /**
   * get config value, returns null if not exists
   * @param String $configKey
   * @return Mixed
   **/
  public static function get(string $configKey, $default=null) {
    $val = self::_getConfigPointer($configKey, $exists);
    if (!$exists) return $default;
    return $val;
  }


  /**
   * returns if configkey is present
   * @param String $configKey
   * @return Boolean
   **/
  public static function has(string $configKey) :bool {
    self::_getConfigPointer($configKey, $exists);
    return $exists;
  }


  /**
   * set a config key
   * @param String $configKey
   * @param Mixed $value
   * @param Boolean|true $overwrite
   * @return Void
   **/
  public static function set(string $configKey, $value, bool $overwrite=true) {
    if (self::has($configKey) && !$overwrite) return;
    self::$_config[$configKey] = $value;
  }

}